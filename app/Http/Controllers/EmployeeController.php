<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

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

        if($request->exists('id_role'))
            $employees = $employees->where('roles.id', $request->id_role);

        if($request->exists('min_date'))
            $employees = $employees->where('users.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
            $employees = $employees->where('users.created_at', '<=', $request->max_date);


        $employees = $employees->select('users.id as id_user', 'employees.id as id_employee', 'users.email as email_user', 'users.username as username_user', 'employees.full_name as full_name_employee', 'users.cellphone_number as cellphone_number_user', 'employees.created_at as created_at_employee', 'employees.updated_at as updated_at_employee')
                                ->get();

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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        //
    }
}
