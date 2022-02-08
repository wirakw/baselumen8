<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
     /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get the authenticated User.
     *
     * @return Response
     */
    public function profile()
    {
        return response()->json(['user' => Auth::user()], 200);
    }

    /**
     * user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|min:10',
        ]);
        $input = $request->all();
        $user = User::where('id', $id)->first();
        $update = $user->update($input);
        if ($update) {
            return response()->json([
                "success" => true,
                "message" => "sukses update user",
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "gagal update user",
            ], 200);
        }
    }

    /**
     * user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDeviceToken(Request $request)
    {
        $this->validate($request, [
            'device_token' => 'required',
        ]);
        $input = $request->all();
        $user = User::where('id', Auth::user()->id)->first();
        $update = $user->update($input);
        if ($update) {
            return response()->json([
                "success" => true,
                "message" => "sukses set device token",
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "gagal set device token",
            ], 200);
        }
    }

    public function updatePhoto(Request $request)
    {
        $userData = Auth::user();
        $this->validate($request, [
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $file = $request->file('photo');
        $filename = $userData->id . '-' . time() . '.' . $file->getClientOriginalExtension();
        $file->move('app/user/', $filename);
        $input['photo'] = $filename;
        $user = User::where('id', $userData->id)->first();
        if (isset($user->photo)) {
            unlink('app/user/' . $user->photo);
        }
        $update = $user->update($input);
        if ($update) {
            return response()->json([
                "success" => true,
                "message" => "success update foto",
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "gagal update foto",
            ], 200);
        }
    }
}
