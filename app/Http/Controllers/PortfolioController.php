<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $portfolio = $request->user()->portfolio;
        
        if (!$portfolio) {
            return response()->json(['message' => 'Portfolio not found'], 404);
        }

        $portfolio->load(['items' => function($query) {
            $query->latest();
        }]);

        return response()->json($portfolio);
    }

    public function update(Request $request)
    {
        $portfolio = $request->user()->portfolio;

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'bio' => 'string',
            'visibility' => 'in:public,private,verified_only',
            'social_links' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldData = $portfolio->toArray();
        $portfolio->update($request->only(['title', 'bio', 'visibility', 'social_links']));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_portfolio',
            'entity_type' => 'portfolio',
            'entity_id' => $portfolio->id,
            'old_data' => $oldData,
            'new_data' => $portfolio->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json($portfolio);
    }

    public function uploadPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|max:2048', // 2MB
            'type' => 'required|in:profile,cover'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $portfolio = $request->user()->portfolio;
        
        $path = $request->file('photo')->store('portfolios/' . $request->user()->id, 'public');

        if ($request->type === 'profile') {
            // Delete old photo
            if ($portfolio->profile_photo) {
                Storage::disk('public')->delete($portfolio->profile_photo);
            }
            $portfolio->profile_photo = $path;
        } else {
            if ($portfolio->cover_photo) {
                Storage::disk('public')->delete($portfolio->cover_photo);
            }
            $portfolio->cover_photo = $path;
        }

        $portfolio->save();

        return response()->json(['photo_url' => Storage::url($path)]);
    }

    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:project,certificate,work_experience,education,assessment',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organization' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'credential_id' => 'nullable|string',
            'file' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $portfolio = $request->user()->portfolio;

        $data = $request->except('file');
        $data['portfolio_id'] = $portfolio->id;
        $data['status'] = 'pending';

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('certificates/' . $request->user()->id, 'public');
            $data['file_path'] = $path;
            $data['file_type'] = $request->file('file')->getMimeType();
            $data['file_size'] = $request->file('file')->getSize();
        }

        $item = PortfolioItem::create($data);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'add_portfolio_item',
            'entity_type' => 'portfolio_item',
            'entity_id' => $item->id,
            'new_data' => $item->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json($item, 201);
    }

    public function updateItem(Request $request, $id)
    {
        $item = PortfolioItem::whereHas('portfolio', function($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($id);

        if ($item->status === 'verified') {
            return response()->json(['message' => 'Cannot update verified items'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'organization' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldData = $item->toArray();
        $item->update($request->only(['title', 'description', 'organization']));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'update_portfolio_item',
            'entity_type' => 'portfolio_item',
            'entity_id' => $item->id,
            'old_data' => $oldData,
            'new_data' => $item->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json($item);
    }

    public function deleteItem(Request $request, $id)
    {
        $item = PortfolioItem::whereHas('portfolio', function($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($id);

        if ($item->status === 'verified') {
            return response()->json(['message' => 'Cannot delete verified items'], 403);
        }

        if ($item->file_path) {
            Storage::disk('public')->delete($item->file_path);
        }

        $item->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'delete_portfolio_item',
            'entity_type' => 'portfolio_item',
            'entity_id' => $id,
            'old_data' => ['title' => $item->title],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'Item deleted successfully']);
    }
}