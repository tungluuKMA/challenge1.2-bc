<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Game;
use App\Models\Exercise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;




class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::all();
        $exercises = auth()->user()->exercises()->get();
        if (auth()->user()->isRole('teacher') || auth()->user()->isRole('admin')) {
            $exercises = Exercise::all();
        }
        $games = Game::all();
        foreach ($games as $game) {
            $game->game_content = '';
            $game->result = '';
            $temp_files = glob( public_path('upload/game/game-' . $game->id . '/*.*') );
            foreach($temp_files as $file) {
                $game->game_content = file_get_contents($file);
                $game->result = str_replace( public_path('upload/game/game-' . $game->id), '', $file);
                $game->result = str_replace('/', '', $game->result);
                $game->result = str_replace('.txt', '', $game->result);
                $game->result = str_replace('-', ' ', $game->result);
                $game->result = ucwords($game->result);
            }
        }
        return view('block.view_user', compact('users', 'exercises', 'games'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $roles = Role::all();
        return view('block.add_user', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        unset($data['_token']);
        $data['password'] = bcrypt($data['password']);
        unset($data['roles']);
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'username' => 'required|max:16|unique:users',
            'password' => 'required|min:6|confirmed',
            'gender' => 'required',
            'sdt' => 'required|regex:/(0)[0-9]{9}/|unique:users'
        ], [
            'name.required' => 'T??n kh??ng ???????c ????? tr???ng',
            'name.max' => 'T??n kh??ng ???????c qu?? d??i',
            'email.required' => 'Email kh??ng ???????c ????? tr???ng',
            'email.email' => 'Email kh??ng ????ng ?????nh d???ng',
            'email.max' => 'Email kh??ng ???????c qu?? d??i',
            'email.unique' => 'Email ???? ???????c s??? d???ng',
            'username.required' => 'Username kh??ng ???????c ????? tr???ng',
            'username.max' => 'Username kh??ng ???????c qu?? d??i',
            'username.unique' => 'Username ???? t???n t???i',
            'password.required' => 'Password kh??ng ???????c ????? tr???ng',
            'password.min' => 'Password ph???i d??i h??n 6 k?? t???',
            'password.confirmed' => 'Password kh??ng tr??ng nhau',
            'gender.required' => 'Gi???i t??nh ch??a ch???n',
            'sdt.required' => 'S??t kh??ng ???????c ????? tr???ng',
            'sdt.regex' => 'S??t kh??ng ????ng ?????nh d???ng',
            'sdt.unique' => 'Sdt ???? ???????c s??? d???ng',
        ]);

            DB::beginTransaction();
            $user = User::create($data);
            $user->roles()->attach($request->roles);
            DB::commit();
            return redirect('/');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $user = User::findOrFail($id);
        $roles = Role::all();
        $listRoles = DB::table('role_user')->where('user_id', $id)->pluck('role_id');
        return view('block.info_user', compact('roles', 'user', 'listRoles'));
    }
    public function updateInfo(Request $request, $id)
    {
        if (!auth()->user()->isRole('student')) {
            $dataValidate['name'] = 'required|max:255';
            $dataValidate['username'] = 'required|max:16|unique:users,username,'.$id.',id';
            $messenger['username.required'] = 'Username kh??ng ???????c ????? tr???ng';
            $messenger['username.max'] = 'Username kh??ng ???????c qu?? d??i';
            $messenger['username.unique'] = 'Username ???? t???n t???i';
            $messenger['name.required'] = 'T??n kh??ng ???????c ????? tr???ng';
            $messenger['name.max'] = 'T??n kh??ng ???????c qu?? d??i';
            $data['username'] = $request->username;
            $data['name'] = $request->name;
        }
        $dataValidate = [
            'email' => 'required|email|max:255|unique:users,email,'.$id.',id',
            'gender' => 'required',
            'sdt' => 'required|regex:/(0)[0-9]{9}/|unique:users,sdt,'.$id.',id',
        ];
        $messenger = [
            'email.required' => 'Email kh??ng ???????c ????? tr???ng',
            'email.email' => 'Email kh??ng ????ng ?????nh d???ng',
            'email.max' => 'Email kh??ng ???????c qu?? d??i',
            'email.unique' => 'Email ???? ???????c s??? d???ng',
            'gender.required' => 'Gi???i t??nh ch??a ch???n',
            'sdt.required' => 'S??t kh??ng ???????c ????? tr???ng',
            'sdt.regex' => 'S??t kh??ng ????ng ?????nh d???ng',
            'sdt.unique' => 'Sdt ???? ???????c s??? d???ng',
        ];

        if ($request->has('password') && $request->password !== null) {
            $dataValidate['password'] = 'required|min:6|confirmed';
            $messenger['password.required'] = 'Password kh??ng ???????c ????? tr???ng';
            $messenger['password.min'] = 'Password ph???i d??i h??n 6 k?? t???';
            $messenger['password.confirmed'] = 'Password kh??ng tr??ng nhau';
            $data['password'] = bcrypt($request->password);
        }

        $check = $request->validate($dataValidate, $messenger);
        
        $data['email'] = $request->email;
        $data['sdt'] = $request->sdt;
        $data['gender'] = $request->gender;
        User::where('id', $id)->update($data);
        return redirect('/');
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $user = User::findOrFail($id);
        $roles = Role::all();
        $listRoles = DB::table('role_user')->where('user_id', $id)->pluck('role_id');
        return view('block.edit_user', compact('roles', 'user', 'listRoles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //

            $dataValidate = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users,email,'.$id.',id',
                'username' => 'required|max:16|unique:users,username,'.$id.',id',
                'gender' => 'required',
                'sdt' => 'required|regex:/(0)[0-9]{9}/|unique:users,sdt,'.$id.',id',
                'roles' => 'required',
            ];
            $messenger = [
                'name.required' => 'T??n kh??ng ???????c ????? tr???ng',
                'name.max' => 'T??n kh??ng ???????c qu?? d??i',
                'email.required' => 'Email kh??ng ???????c ????? tr???ng',
                'email.email' => 'Email kh??ng ????ng ?????nh d???ng',
                'email.max' => 'Email kh??ng ???????c qu?? d??i',
                'email.unique' => 'Email ???? ???????c s??? d???ng',
                'username.required' => 'Username kh??ng ???????c ????? tr???ng',
                'username.max' => 'Username kh??ng ???????c qu?? d??i',
                'username.unique' => 'Username ???? t???n t???i',
                'gender.required' => 'Gi???i t??nh ch??a ch???n',
                'sdt.required' => 'S??t kh??ng ???????c ????? tr???ng',
                'sdt.regex' => 'S??t kh??ng ????ng ?????nh d???ng',
                'sdt.unique' => 'Sdt ???? ???????c s??? d???ng',
                'roles.required' => 'Vai tr?? kh??ng ???????c ????? tr???ng',
            ];

            $check = $request->validate($dataValidate, $messenger);
            
            $data['username'] = $request->username;
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['sdt'] = $request->sdt;
            $data['gender'] = $request->gender;
            DB::beginTransaction();
            User::where('id', $id)->update($data);
            DB::table('role_user')->where('user_id', $id)->delete();
            $userUpdate = User::find($id);
            $userUpdate->roles()->attach($request->roles);
            DB::commit();
            return redirect('/');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
 
            DB::beginTransaction();
            $user = User::find($id);
            $user->delete();
            $user->roles()->detach();
            DB::commit();
            return redirect('/');
       
    }
}
