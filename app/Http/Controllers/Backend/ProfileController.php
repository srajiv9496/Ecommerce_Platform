<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use File;

class ProfileController extends Controller
{
    public function index()
    {
        return view('admin.profile.index');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,'.Auth::user()->id],
            'image' => ['image', 'max:2048']
        ]);

        $user = Auth::user();

        if($request->hasFile('image')){
            //checking for the profile pic, whether it's already present , if yes then update else delete.
            if(File::exists(public_path($user->image))){
                File::delete(public_path($user->image));
            }
            //uplaoding the profile pic
            $image = $request->image;
            $imageName = rand().'_'.$image->getClientOriginalName();
            $image->move(public_path('uploads'),$imageName);

            $path = "/uploads/".$imageName;

            $user->image=$path;

        }

        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
//adding toastr as an inbult prompt showing the status of the job.
        toastr()->success('Profile Updated Successfully!');

        return redirect()->back();
    }

//password validation
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed']
        ]);

        $request->user()->update([
            'password' => bcrypt($request->password)
        ]);
        toastr()->success('Profile Password Updated Successfully!');
        return redirect()->back();
    }
}
