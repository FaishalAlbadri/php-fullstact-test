<?php
namespace App\Http\Controllers;

use App\Models\MyClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class MyClientController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:100|unique:my_client,slug',
            'is_project' => 'required|in:0,1',
            'self_capture' => 'required|string|max:1',
            'client_prefix' => 'required|string|max:4',
            'client_logo' => 'nullable|image|max:2048',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('client_logo')) {
            $validatedData['client_logo'] = $request->file('client_logo')->store('client-logos', 's3');
        }

        $client = MyClient::create($validatedData);

        Redis::set("client:{$client->slug}", json_encode($client));

        return response()->json($client, 201);
    }

    public function show($slug)
    {
        $cachedClient = Redis::get("client:$slug");
        if ($cachedClient) {
            return response()->json(json_decode($cachedClient), 200);
        }

        $client = MyClient::where('slug', $slug)->firstOrFail();

        Redis::set("client:$slug", json_encode($client));

        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        $client = MyClient::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:250',
            'slug' => 'sometimes|required|string|max:100|unique:my_client,slug,' . $id,
            'is_project' => 'sometimes|required|in:0,1',
            'self_capture' => 'sometimes|required|string|max:1',
            'client_prefix' => 'sometimes|required|string|max:4',
            'client_logo' => 'nullable|image|max:2048',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('client_logo')) {
            if ($client->client_logo !== 'no-image.jpg') {
                Storage::disk('s3')->delete($client->client_logo);
            }
            $validatedData['client_logo'] = $request->file('client_logo')->store('client-logos', 's3');
        }

        Redis::del("client:{$client->slug}");

        $client->update($validatedData);

        Redis::set("client:{$client->slug}", json_encode($client));

        return response()->json($client);
    }

    public function destroy($id)
    {
        $client = MyClient::findOrFail($id);

        $client->update(['deleted_at' => now()]);

        Redis::del("client:{$client->slug}");

        return response()->json(['message' => 'Client deleted'], 200);
    }
}
