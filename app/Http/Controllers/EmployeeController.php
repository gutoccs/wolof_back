<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelRoles\Models\Role;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employees = Employee::leftJoin('users', 'employees.user_id', '=', 'users.id')
                        ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                        ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                        ->whereIn('roles.slug', ['ceo', 'cto', 'wolof.employee']);


        if($request->exists('id_user'))
            $employees = $employees->where('users.id', $request->id_user);

        if($request->exists('id_employee'))
            $employees = $employees->where('employees.id', $request->id_employee);

        if($request->exists('id_public_employee'))
            $employees = $employees->where('employees.id_public', $request->id_public_employee);

        if($request->exists('id_role'))
            $employees = $employees->where('roles.id', $request->id_role);

        if($request->exists('min_date'))
            $employees = $employees->where('users.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
            $employees = $employees->where('users.created_at', '<=', $request->max_date);

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee']))
        {
            if($request->exists('flag_login'))
            {
                if(in_array($request->flag_login, [0, 1]))
                    $employees = $employees->where('users.flag_login', $request->flag_login);
            }
        }

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee']))
            $employees = $employees->select('users.id as id_user', 'employees.id as id_employee', 'employees.id_public as id_public_employee','users.email as email_user', 'users.username as username_user', 'employees.full_name as full_name_employee', 'users.cellphone_number as cellphone_number_user', 'users.flag_login as flag_login_user', 'users.observation_flag_login as observation_flag_login_user', 'employees.created_at as created_at_employee', 'employees.updated_at as updated_at_employee');
        else
            $employees = $employees->select('users.id as id_user', 'employees.id as id_employee', 'employees.id_public as id_public_employee','users.email as email_user', 'users.username as username_user', 'employees.full_name as full_name_employee', 'users.cellphone_number as cellphone_number_user', 'employees.created_at as created_at_employee', 'employees.updated_at as updated_at_employee');

        $employees = $employees->get();

        return response()->json(
        [
            'status'        =>  'success',
            'employees'     =>  $employees
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'email'                     =>  'required|email|unique:users',
            'password'                  =>  'required|min:8|confirmed',
            'password_confirmation'     =>  'required|same:password',
            'cellphone_number'          =>  'required|string|between:4,32|unique:users',
            'full_name'                 =>  'required|string|between:4,128',
            'role_id'                   =>  'required|numeric|exists:roles,id|in:1,2,3',
        ],
        [
            'email.required'            =>  'El Correo Electrónico es requerido',
            'email.email'               =>  'Debe indicar un Correo Electrónico válido',
            'email.unique'              =>  'El Correo Electrónico ya está en uso',
            'password.required'         =>  'La contraseña es requerida',
            'password.min'              =>  'La longitud mínima de la Contraseña es de 8 caracteres',
            'password.confirmed'        =>  'Las Contraseñas no coinciden',
            'password_confirmation.required'    => 'La confirmación de la Contraseña es requerida',
            'password_confirmation.same'        => 'La confirmación de la Contraseña y la Contraseña no coinciden',
            'cellphone_number.required'         =>  'El Teléfono Celular es requerido',
            'cellphone_number.string'           =>  'El Teléfono Celular tiene un formato inválido ',
            'cellphone_number.between'          =>  'La longitud del Teléfono Celular debe ser entre 4 y 32 caracteres',
            'cellphone_number.unique'          =>  'El Teléfono Celular ya está en uso',
            'full_name.required'                 =>  'El Nombre Completo es requerido',
            'full_name.string'                   =>  'El Nombre Completo es inválido',
            'full_name.between'                  =>  'La longitud del Nombre Completo es entre 4 y 128 caracteres',
            'role_id.required'                  =>  'El ID del Rol es requerido',
            'role_id.numeric'                   =>  'El ID del Rol debe ser numérico',
            'role_id.exists'                    =>  'El Rol No Existe en la BD',
            'role_id.in'                        =>  'El Rol No es válido para un empleado de Wolof'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $token = Str::random(24);

        $user = new User();
        $user->email = strtolower($request->email);
        $user->password = bcrypt($request->password);
        $user->cellphone_number = $request->cellphone_number;
        $user->flag_login = true;
        $user->observation_flag_login = 'Email sin verificar - ' . $token;

        if(!$user->save())
            return response()->json(['errors' => 'No se pudo crear el Usuario del Empleado'], 422);

        $employee = new Employee();
        $employee->user_id = $user->id;
        $employee->full_name = $request->full_name;

        $auxIdPublic;
        while (true)
        {
            $auxIdPublic = Str::random(24);
            $auxC = Client::where('id_public', $auxIdPublic)->count();
            $auxE = Employee::where('id_public', $auxIdPublic)->count();
            $auxM = Merchant::where('id_public', $auxIdPublic)->count();

            if($auxC == 0 && $auxE == 0 && $auxM == 0)
                break;
        }
        $employee->id_public = $auxIdPublic;

        if(!$employee->save())
        {
            $user->forceDelete();
            return response()->json(['errors'   =>  'No se pudo crear al empleado'], 422);
        }

        $newRole = config('roles.models.role')::find($request->role_id);
        $employee->user->attachRole($newRole);

        return response()->json(['status' => 'success'], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show($idPublicEmployee)
    {
        if(Employee::where('id_public', $idPublicEmployee)->count() == 0)
            return response()->json(['errors'   =>  'El Empleado no existe'], 422);

        $employee = Employee::leftJoin('users', 'employees.user_id', '=', 'users.id')
                            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                            ->whereIn('roles.slug', ['ceo', 'cto', 'wolof.employee'])
                            ->where('employees.id_public', $idPublicEmployee);

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee']))
            $employee = $employee->select('users.id as id_user', 'employees.id as id_employee', 'employees.id_public as id_public_employee', 'users.email as email_user', 'users.username as username_user', 'employees.full_name as full_name_employee', 'users.cellphone_number as cellphone_number_user', 'users.flag_login as flag_login_user', 'users.observation_flag_login as observation_flag_login_user', 'employees.created_at as created_at_employee', 'employees.updated_at as updated_at_employee');
        else
            $employee = $employee->select('users.id as id_user', 'employees.id as id_employee', 'employees.id_public as id_public_employee', 'users.email as email_user', 'users.username as username_user', 'employees.full_name as full_name_employee', 'users.cellphone_number as cellphone_number_user', 'employees.created_at as created_at_employee', 'employees.updated_at as updated_at_employee');

        $employee = $employee->first();


        return response()->json(
            [
                'status'        =>  'success',
                'employee'      =>  $employee
            ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idPublicEmployee)
    {

        $employee = Employee::where('id_public', $idPublicEmployee)->first();

        if(!$employee)
            return response()->json(['errors'   =>  'El Empleado no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'email'                     =>  'email',
            'password'                  =>  'min:8',
            'username'                  =>  'alpha_dash|between:4,64',
            'cellphone_number'          =>  'string|between:4,32',
            'full_name'                 =>  'string|between:4,128',
        ],
        [
            'email.email'               =>  'Debe indicar un Correo Electrónico válido',
            'password.min'              =>  'La longitud mínima de la Contraseña es de 8 caracteres',
            'username.alpha_dash'       =>  'Solo se aceptan caracteres alfanuméricos, guiones y guiones bajos',
            'username.between'          =>  'La longitud del Nombre de Usuario debe ser entre 4 y 64 caracteres',
            'cellphone_number.string'   =>  'El Teléfono Celular tiene un formato inválido ',
            'cellphone_number.between'  =>  'La longitud del Teléfono Celular debe ser entre 4 y 32 caracteres',
            'full_name.string'          =>  'El Nombre Completo es inválido',
            'full_name.between'         =>  'La longitud del Nombre Completo es entre 4 y 128 caracteres',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if($request->exists('email'))
        {
            if(User::where('email', $request->email)->where('id', '!=', $employee->user->id)->count() == 1)
                return response()->json(['errors'   =>  'El Correo Electrónico ya está siendo utilizado'], 422);

            if(User::where('email', $request->email)->count() == 0 && $employee->user->email != $request->email)
                $employee->user->email = $request->email;
        }

        if($request->exists('password'))
            $employee->user->password = bcrypt($request->password);

        if($request->exists('username'))
        {
            if(User::where('username', $request->username)->where('id', '!=', $employee->user->id)->count() == 1)
                return response()->json(['errors'   =>  'El Nombre de Usuario ya está siendo utilizado'], 422);

            if(User::where('username', $request->username)->count() == 0  && $employee->user->username != $request->username)
                $employee->user->username = strtolower($request->username);
        }

        if($request->exists('cellphone_number'))
        {
            if(User::where('cellphone_number', $request->cellphone_number)->where('id', '!=', $employee->user->id)->count() == 1)
                return response()->json(['errors'   =>  'El Teléfono Celular ya está siendo utilizado'], 422);

            if(User::where('cellphone_number', $request->cellphone_number)->count() == 0  && $employee->user->cellphone_number != $request->cellphone_number)
                $employee->user->cellphone_number = $request->cellphone_number;
        }

        if($request->exists('full_name'))
            $employee->full_name = $request->full_name;


        if($employee->save() && $employee->user->save())
            return response()->json(['status' => 'success'], 200);

        return response()->json(['errors'   =>  'No se pudo acualizar al Empleado'], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy($idPublicEmployee)
    {
        // TODO: Faltan realizar validaciones antes de eliminarlo

        $employee = Employee::where('id_public', $idPublicEmployee)->first();

        if(!$employee)
            return response()->json(['errors'   =>  'El Empleado no existe'], 422);

        if($employee->user->delete())
            $employee->delete();

        return response()->json(['status' => 'success'], 200);
    }

    public function changeRole(Request $request, $idPublicEmployee)
    {
        $employee = Employee::find('id_public', $idPublicEmployee)->first();

        if(!$employee)
            return response()->json(['errors'   =>  'El Empleado no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'role_id'                   =>  'required|numeric|exists:roles,id|in:1,2,3',
        ],
        [
            'role_id.required'          =>  'El ID del Rol es requerido',
            'role_id.numeric'           =>  'El ID del Rol debe ser numérico',
            'role_id.exists'            =>  'El Rol No Existe en la BD',
            'role_id.in'                =>  'El Rol No es válido para un empleado de Wolof'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        // Elimino todos los roles porque por ahora cada usuario solo tiene un rol
        $employee->user->detachAllRoles();

        $newRole = config('roles.models.role')::find($request->role_id);
        $employee->user->attachRole($newRole);

        return response()->json(['status' => 'success'], 200);
    }
}
