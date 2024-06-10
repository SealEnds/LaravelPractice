<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Http\Requests\StoreSongRequest;
use App\Http\Requests\UpdateSongRequest;
use App\Http\Requests\UploadSongRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    const HEADERS = ['Content-Type' => 'application/JSON'];
    const PLAY_COUNT_INIT = 0;
    const LIMIT_ACCEPTED_VALUES = [10, 25, 50, 100];
    const DISK = "/songs";
    const INCREMENT = 1;
    
    public function index($limit = 10)
    {
        $limit = (int)$limit;
        if (!in_array($limit, self::LIMIT_ACCEPTED_VALUES)) $limit = 10;
        try {
            $songs = Song::orderBy('created_at', 'desc')->paginate($limit);

            $response = ["status" => "success", "data" => $songs];
            $httpStatus = 200;
            if (!is_object($songs) || empty($songs)) {
                $response = ["status" => "error", "message" => "Song not found."];
                $httpStatus = 404;
            }
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Song not found."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(StoreSongRequest $request)
    {
        try {
            $request = $request -> validated();
            $song = Song::create([
                'title' => $request['title'],
                'play_count' => self::PLAY_COUNT_INIT,
                'description' => $request['description']
            ]);
            $song->save();

            $response = ["status" => "success", "data" => $song];
            $httpStatus = 200;
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error saving song."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

    public function upload(UploadSongRequest $request, $id)
    {
        try{
            $song = Song::where('id', $id)->first();
            if (empty($song)) {
                $response = ["status" => "error", "message" => "Song not found."];
                $httpStatus = 404;
                return response()->json($response, $httpStatus, self::HEADERS);
            } 
            $this->destroyFile($id);
            $request = $request->validated();
            $songFile = $request['file'];
            $mimetype = $request['file']->extension();
            $uuid = Uuid::uuid4();
            $fileName = $uuid->toString().'.'.$mimetype;
            $filePath = self::DISK."$fileName";
            Storage::disk('local')->put($filePath, File::get($songFile));
            $song->file_path = $filePath;
            $song->update();
            $response = ["status" => "success", "data" => $request];
            $httpStatus = 200;
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error uploading song file."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

    public function download($id)
    {
        try {
            $song = Song::where('id', $id)->first();
            $filePath = $song->file_path;

            $response = ["status" => "success", "data" => $song];
            $httpStatus = 200;
            if (empty($song)) {
                $response = ["status" => "error", "message" => "Song not found."];
                $httpStatus = 404;
            }
            return Storage::download($filePath);
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error downloading."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

    public function destroyFile($id)
    {
        try {
            $song = Song::where('id', $id)->first();
            $filePath = $song->file_path;

            $deleted = Storage::delete($filePath);
            $response = ["status" => "error", "message" => "File not found."];
            $httpStatus = 404;
            if($deleted) {
                $song->file_path = null;
                $song->update();
                $response = ["status" => "success", "message" => "File deleted."];
                $httpStatus = 200;
            }
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error deleting file."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $song = Song::where('id', $id)->first();

            $response = ["status" => "success", "data" => $song];
            $httpStatus = 200;
            if (empty($song)) {
                $response = ["status" => "error", "message" => "Song not found."];
                $httpStatus = 404;
            }
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error getting song."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSongRequest $request, $id)
    {
        try {
            $request = $request->validated(); 
            $song = Song::where('id', $id)->first();
            $updated = $song->update($request);
            
            $response = ["status" => "error", "message" => "Song not found."];
            $httpStatus = 404;
            if($updated) {
                $response = ["status" => "success", "data" => $song];
                $httpStatus = 200;
            }
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error updating song."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Song $song, $id)
    {
        try {
            $song = Song::where('id', $id)->first();

            $response = ["status" => "error", "message" => "Song not found."];
            $httpStatus = 404;
            if(is_object($song) && !empty($song)) {
                $destroyed = $song->delete();
                if($destroyed) {
                    $response = ["status" => "success", "data" => $song];
                    $httpStatus = 200;
                }
            }
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error deleting song."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

    public function play($id) 
    {
        try {
            $song = Song::where('id', $id)->first();
            if (empty($song)) {
                $response = ["status" => "error", "message" => "Song not found."];
                $httpStatus = 404;
                return response()->json($response, $httpStatus, self::HEADERS);
            }
            $song->play_count += self::INCREMENT;
            $song->update();
            $response = ["status" => "success", "data" => $song];
            $httpStatus = 200;
        } catch (Exception $e) {
            Log::error('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error getting song."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, self::HEADERS);
    }

}
