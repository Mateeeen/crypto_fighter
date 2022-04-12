<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class HomeController extends Controller
{

/**
 * Show the form for creating a new resource.
 *
 * @return \Illuminate\Http\Response
 */

    public function create()
    {
//
    }

/**
 * Store a newly created resource in storage.
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\Response
 */
    public function getAllEmployees()
    {
        $employees = DB::table('employees')->get();
        return view('list_employees', compact('employees'));
    }

    public function postAddtime(Request $request)
    {
        $this->validate($request, [
            'monday_start_time' => 'required',
            'monday_end_time' => 'required',
            'tuesday_start_time' => 'required',
            'tuesday_end_time' => 'required',
            'wednesday_start_time' => 'required',
            'wednesday_end_time' => 'required',
            'thursday_start_time' => 'required',
            'thursday_end_time' => 'required',
            'friday_start_time' => 'required',
            'friday_end_time' => 'required',
            'saturday_start_time' => 'required',
            'saturday_end_time' => 'required',
        ]);

        DB::table('times')->update([
            'monday_start_time' => $request->monday_start_time,
            'monday_end_time' => $request->monday_end_time,
            'tuesday_start_time' => $request->tuesday_start_time,
            'tuesday_end_time' => $request->tuesday_end_time,
            'wednesday_start_time' => $request->wednesday_start_time,
            'wednesday_end_time' => $request->wednesday_end_time,
            'thursday_start_time' => $request->thursday_start_time,
            'thursday_end_time' => $request->thursday_end_time,
            'friday_start_time' => $request->friday_start_time,
            'friday_end_time' => $request->friday_end_time,
            'saturday_start_time' => $request->saturday_start_time,
            'saturday_end_time' => $request->saturday_end_time,

        ]);

        return redirect()->back()->with('info', 'You have Added time Successfully!');
    }

    public function postAddEmployee(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'province' => 'required',
            'mobile' => 'required|integer|min:12|unique:employees',
            'emergency_number' => 'digits_between:7,12|required',
            'salary' => 'required',
            'working_hours' => 'required',
            'type'=> 'required',
        ]);

        DB::table('employees')->insert([
            'surname' => $request->surname,
            'name' => $request->name,
            'address' => $request->address,
            'province' => $request->province,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'account_number' => $request->account_number,
            'account_type' => $request->account_type,
            'bank' => $request->bank,
            'emergency_number' => $request->emergency_number,
            'salary' => $request->salary,
            'working_hours' => $request->working_hours,
            'workers_type'=> $request->type,

        ]);

        return redirect()->back()->with('info', 'You have Added User Successfully!');
    }

    public function postCreateTask(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',

        ]);

// dd($request->emergency_number);
        DB::table('tasks_and_subtasks')->insert([
            'parent_id' => 0,
            'task' => $request->name,
            'description' => $request->description,

        ]);

        return redirect()->back()->with('info', 'You have Added Task Successfully!');
    }

    public function postCreateSubTask(Request $request)
    {

        $this->validate($request, [
            'task_id' => 'required',
            'subtasks' => 'required',
            'description' => 'required',

        ]);

// dd($request->emergency_number);
        DB::table('tasks_and_subtasks')->insert([

            'parent_id' => $request->task_id,
            'task' => $request->subtasks,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('message', 'You have Added SubTask Successfully!');
    }

    public function SearchProject(Request $request)
    {
        $fecha = $request->fecha;
        $work = $request->work;
        if(isset($request->fecha) && isset($request->work)){

            $projects = DB::table('projects')->where('status', $request->work)->where('start_date', $request->fecha)->get();
            foreach($projects as $row){
                $workers = DB::table('daily_work_performance')->where('project_id',$row->id)->get();
                $labour = null;
                foreach($workers as $value){
                    $labour += DB::table('employees')->where('id',$value->employee_id)->pluck('salary')->first() * $value->working_time;
                }
                $recent_task = DB::table('project_task')->where('project_id',$row->id)->pluck('task_name')->first();
                 $total_tasks = DB::table('daily_work_performance')->where('project_id',$row->id)->count();
                    $row->progress = $row->progress = DB::table('daily_work_performance')->where('project_id',$row->id)->sum('working_time') / DB::table('project_operator')->where('project_id',$row->id)->sum('total_hour') * 100;
                $row->labour_cost = $labour;
                $row->current_task = $recent_task;                
            }
            return view('search_project', compact('projects','work','fecha'));
            }

            else if(isset($request->fecha)){

                $projects = DB::table('projects')->where('start_date', $request->fecha)->get();
                foreach($projects as $row){
                    $workers = DB::table('daily_work_performance')->where('project_id',$row->id)->get();
                    $labour = null;
                    foreach($workers as $value){
                        $labour += DB::table('employees')->where('id',$value->employee_id)->pluck('salary')->first() * $value->working_time;
                    }
                    $recent_task = DB::table('project_task')->where('project_id',$row->id)->pluck('task_name')->first();

                    $total_tasks = DB::table('daily_work_performance')->where('project_id',$row->id)->count();
                    $row->progress = $row->progress = DB::table('daily_work_performance')->where('project_id',$row->id)->sum('working_time') / DB::table('project_operator')->where('project_id',$row->id)->sum('total_hour') * 100;
                    $row->labour_cost = $labour;
                    $row->current_task = $recent_task;
                    
                }
                
                return view('search_project', compact('projects','fecha'));
                
            }
            
            else if (isset($request->work)){
                $projects = DB::table('projects')->where('status', $request->work)->get();
                foreach($projects as $row){
                    $workers = DB::table('daily_work_performance')->where('project_id',$row->id)->get();
                    $labour = null;
                    foreach($workers as $value){
                        $labour += DB::table('employees')->where('id',$value->employee_id)->pluck('salary')->first() * $value->working_time;
                    }
                    $recent_task = DB::table('project_task')->where('project_id',$row->id)->pluck('task_name')->first();

                     $total_tasks = DB::table('daily_work_performance')->where('project_id',$row->id)->count();
                    $row->progress = $row->progress = DB::table('daily_work_performance')->where('project_id',$row->id)->sum('working_time') / DB::table('project_operator')->where('project_id',$row->id)->sum('total_hour') * 100;
                    $row->labour_cost = $labour;
                    $row->current_task = $recent_task;
                    
                }
                
                return view('search_project', compact('projects','work'));


            }
                
            return redirect()->back();

    }

    public function Search(Request $request)
    {
        $this->validate($request, [
            'project' => 'required',
            'date' => 'required',
        ]);
        // To cenvert months into date
        $date = explode('-',$request->date);
        $date2 = $request->date;
        $project = $request->project;
        
        $tasks_id = DB::table('daily_work_performance')->where('project_id',$request->project)->whereMonth('date',$date[1])->get()->pluck('task_id');
        
        if(count($tasks_id) != 0){
            $tasks = DB::table('project_task')->where('id',$tasks_id)->get();
            $projects = DB::table('projects')->get();

            return view('performance_of_work', compact('tasks','projects','date2','date','project'));

        }
        return redirect()->back()->with('info', 'No hay tareas para este filtro');   
    }

    public function postAssignTask(Request $request)
    {

        $this->validate($request, [
            'project_id' => 'required',
            'task_id' => 'required',
            'employee_id_1' => 'required',
            'date' => 'required',
        ]);

        $employees = $request->get('employee_id_1');
   
        foreach ($employees as $key => $value) {        
            DB::table('assignments')->insert([

                'project_id' => $request->project_id,
                'task_id' => $request->task_id,
                'date' => $request->date,
                'employee_id_1' => $value,
            ]);
        }       

        return redirect()->back()->with('info', 'You have Assigned Task Successfully!');
    }

    public function postAddfabrication(Request $request)
    {

        $this->validate($request, [
            'manufacturer' => 'required',
            'types_of_material' => 'required',
            'quality' => 'required',


        ]);

// dd($request->emergency_number);
        DB::table('fabrications')->insert([

            'manufacturer_name' => $request->manufacturer,
            'types_of_materials' => $request->types_of_material,
            'quality_of_finishes' => $request->quality,



        ]);

        return redirect()->back()->with('info', 'You have Added Fabrication Successfully!');
    }
    public function create_service(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
            'model' => 'required',
            'time' => 'required',
            'operator' => 'required',



        ]);

// dd($request->emergency_number);
        DB::table('services')->insert([

            'name' => $request->name,
            'type_operators' => $request->type,
            'model' => $request->model,
            'time' => $request->time,
            'operators' => $request->operator,




        ]);

        return redirect()->back()->with('info', 'You have Added Service Successfully!');
    }

    public function add_user(Request $request)
    {

        $this->validate($request, [
            'username' => 'required|unique:users',
            'password' => 'min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'min:6',
        ]);

        DB::table('users')->insert([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'new_employee' => $request->has('new_employee') ? 1 : 0,
            'list_employees' => $request->has('list_employees') ? 1 : 0,
            'history_employees' => $request->has('history_employees') ? 1 : 0,
            'project_list' => $request->has('project_list') ? 1 : 0,
            'production' => $request->has('production') ? 1 : 0,
            'dedicated_time' => $request->has('dedicated_time') ? 1 : 0,
            'create_tool' => $request->has('create_tool') ? 1 : 0,
            'list_tools' => $request->has('list_tools') ? 1 : 0,
            'history_tools' => $request->has('history_tools') ? 1 : 0,
            'create_state' => $request->has('create_state') ? 1 : 0,
        ]);

        return redirect()->back()->with('info', 'You have Added User Successfully!');
    }
    public function post_daily_worker_performance(Request $request)
    {
        $this->validate($request,
            [
                'employee_id' => 'required',
                'projects' => 'required',
                'tasks' => 'required',
                

            ]);

        
        if(!isset($request->attendance) && !isset($request->break)){
            $present = "0";
        
        }
        else if($request->attendance == 'on'){
            $present = "1";
        }
        else if($request->break == 'on'){
            $present = "2";
        }

        DB::table('daily_work_performance')->insert([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'attendance_status' => $present,
            'project_id' => $request->projects,
            'task_id' => $request->tasks,
            'task_requirement' => $request->requirements,
            'working_time' => $request->working_time,
            'finished_unit' => $request->finished_units,
            'objective' => $request->objective,
            'failed_drives' => $request->faulty_units,
            'valorized_loss' => $request->valued_loss,
            'comment' => $request->commentary,

        ]);

        return redirect()->back()->with('info', 'Successfully Added');
    }
    

    public function postAddProject(Request $request)
    {
        // $this->validate($request, [
        //     'project' => 'required',
        //     'location' => 'required',
        //     'customer' => 'required',
        //     'contact_person' => 'required|min:11',
        //     'engineer_incharge' => 'required',
        //     'amount' => 'required',
        //     'start_date' => 'required',
        //     'delivery_date' => 'required|after:start_date',
        //     'video' => 'required',
        //     'product_to_be_manufactured'  => 'required',


        // ]);
        if($request->start_date >= $request->delivery_date){
            return redirect()->back()->with('info', 'Start date should be GREATER THAN Delivery date');

        }

        if ($request->file('video')) {
            $file = $request->file('video');
            $filename = $file->getClientOriginalName();
            $path = public_path() . '/project';
            $file->move($path, $filename);

            DB::table('projects')->insert([
                'project' => $request->project,
                'location' => $request->location,
                'customer' => $request->customer,
                'contact_person' => $request->contact_person,
                'engineer_incharge' => $request->engineer_incharge,
                'amount' => $request->amount,
                'start_date' => $request->start_date,
                'delivery_date' => $request->delivery_date,
                'file' => $filename,
                'product_manufacturing' => $request->product_to_be_manufactured,
 
            ]);

            $items = $request->get('name_task');
            $project_id = DB::table('projects')->orderby('id', 'desc')->pluck('id')->first();
            $c = 1;
            foreach ($items as $i => $item) {

                if ($request->file('file')[$i]) {
                    $file = $request->file('file')[$i];
                    $filename = $file->getClientOriginalName();
                    $path = public_path() . '/task';
                    $file->move($path, $filename);
                    
                    
                    
                    DB::table('project_task')->insert([
                        'task_number' => $c,
                        'project_id' => $project_id,
                        'task_name' => $item,
                        'location' => $request->get('task_location')[$i],
                        'directions' => $request->get('task_directions_operator')[$i],
                        'target_quantity' => $request->get('target_quantity')[$i],
                        'file' => $filename,
         
                    ]);

                    $task_id = DB::table('project_task')->orderby('id', 'desc')->pluck('id')->first();

                    $operators = $request->get('type_operator');
                    foreach ($operators as $i => $operator) { 
                        DB::table('project_operator')->insert([
                            'operator_type' => $request->get('type_operator')[$i],
                            'total_hour' => $request->get('total_hours')[$i],
                            'number_of_operator' => $request->get('operator_number')[$i],
                            'task_id' => $task_id,
                            'project_id' => $project_id,
                        ]);
                    }
                }

                else { 
                    dd($filename[$i]);
                }
                $c = $c + 1;
    
            };
            



            return redirect()->back()->with('info', 'You have Added Project Successfully!');


        };

        return redirect()->back()->with('info', 'Something Wrong');

    }

    public function postchangeStatus(Request $request)
    {
        DB::table('projects')->where('id', $request->id)->update([
            'status' => $request->status]);
        return redirect()->back()->with('info', 'Successfully Updated Status');
    }

    public function edit_user($id)
    {
        $edit_user = DB::table('users')->where('id', $id)->first();
        return view('editpage', compact("edit_user"));
    }
    public function update(Request $request)
    {
        $this->validate(request(), [
            'username' => 'required',
        ]);

        DB::table('users')->where('id', $request->id)->update([
            'username' => $request->username,
            'new_employee' => $request->has('new_employee') ? 1 : 0,
            'list_employees' => $request->has('list_employees') ? 1 : 0,
            'history_employees' => $request->has('history_employees') ? 1 : 0,
            'project_list' => $request->has('project_list') ? 1 : 0,
            'production' => $request->has('production') ? 1 : 0,
            'dedicated_time' => $request->has('dedicated_time') ? 1 : 0,
            'create_tool' => $request->has('create_tool') ? 1 : 0,
            'list_tools' => $request->has('list_tools') ? 1 : 0,
            'history_tools' => $request->has('history_tools') ? 1 : 0,
            'create_state' => $request->has('create_state') ? 1 : 0,
        ]);
        return redirect('user')->with('info', 'You have Edited User Successfully!');
    }

    public function postRegisterMaterials(Request $request)
    {
        $this->validate(request(), [
            'invoice' => 'required',
            'date' => 'required',
            'supplier' => 'required',

        ]);

        $items = $request->get('item');

        foreach ($items as $i => $item) {
            DB::table('register_materials')->insert([
                'invoice' => $request->invoice,
                'date' => $request->date,
                'supplier' => $request->supplier,
                'item' => $items[$i],
                'quantity' => $request->get('quantity')[$i],
                'price' => $request->get('price')[$i],
            ]);

        }

        return redirect()->back()->with('info', 'You have Registerd Materials Successfully!');
    }

    public function delete_user($id)
    {
        DB::table('users')->where('id', $id)->delete();
        return redirect()->back()->with('info', 'You have Deleted User Successfully!');
    }

    public function gettest()
    {
        $view_users = DB::table('users')->where('username', '!=', 'admin')->get();
        return view('users', compact("view_users"));
    }

    public function daily_worker_performance()
    {
        $employees = DB::table('employees')->orderby('name','asc')->get();
        return view('daily_worker_performance', compact('employees'));
    }

    


    public function ajax(Request $request)
    {
        $data = $_GET['datavalue'];
        echo "{{$data}}";
        // return response()->json(['result'=>$request->surname]);
    }

    public function Change_status($id)
    {
        DB::table('project_task')->where('id',$id)->update([
            'task_status' => 1,

        ]);

        return redirect()->back()->with('info', 'El estado cambió con éxito');

    }

    public function samim(Request $request)
    {
        if($request->mapLat =='' && $request->mapLong ==''){
            // Get lat long from google
            $address = str_replace(" ", "+", $request->location);
    
            $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyD6hpJXDlw2hJrqqLb7D4jrvNCVieSsfh4");
            dd($json);
            $json = json_decode($json);
    
            $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
            $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
            dd($lat);
    }
    
    
    // function to get  the address
    function get_lat_long($address){
    
        
    }

    }

    public function Change_statuss($id)
    {
        DB::table('project_task')->where('id',$id)->update([
            'task_status' => 0,

        ]);

        return redirect()->back()->with('info', 'El estado cambió con éxito');

    }

    public function create_materialas(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required',
            'category' => 'required',
            'model' => 'required',

        ]);
        $pieces = explode(',', $request->model);
        


        DB::table('materials')->insert([
            'name' => $request->name,
            'category' => $request->category,
            'model' => $pieces[0],
            'description' => $request->description,
            'height' => $request->height,
            'length' => $request->length,
            'width' => $request->width,
            'depth' => $request->depth,
            'diameter' => $request->diameter,
            
        ]);
        $id = DB::table('materials')->orderby('id','desc')->first();
        $code = $pieces[1].$id->id;

        DB::table('materials')->where('id',$id->id)->update([
            'code' => $code,
            
            
        ]);

        return redirect()->back()->with('info', 'You have Registerd Materials Successfully!');
    }

    public function getpdf(Request $request){
        $mpdf = new \Mpdf\Mpdf();

        $cart_data = DB::table('carts')->where('user_id',auth()->user()->id)->get();
        $name = $request->name;
        $number = $request->number;
        $address = $request->address;

        $data = '';
        $loop = '';
        $amount = 0;

        $data .=  "<h3>Reciept</h3> <br>
                  Client Name: ".$name." <br>
                  Client Mobile Number: ".$number." <br>
                  Client Address: ".$address." <br>
                  ";
                  $data .= "<div class='card'>
                  <div class='card-body'><table>
                  <thead>
                  <tr>
                      <th>Code</th>
                      <th>Nombre</th>
                      <th>Precio por unidad</th>
                      


                  </tr>
              </thead>
              <tbody>";
              if(count($cart_data) == 0){
                return redirect()->back()->with('info', 'Cart Empty');
              } 
              else{

              
                  
                      foreach($cart_data as $cart) {
                        
                          $name = DB::table("materials")->where("id",$cart->product_id)->first();
                          $data .= 
                          " 
                          <tr>
                          
                          <td>".$name->code."</td>
                          <td>".$name->name."</td>
                          <td>".$cart->per_unit_price."</td>
                          
                          </tr>
                          ";
                          $amount += $cart->per_unit_price;
                      };
                      $data .= "</tbody>
                      </table></div></div><div><br><div>Amount = ".$amount."</div></div> ";
                    }    


        $mpdf->WriteHTML($data);
        $mpdf->Output('my.pdf','D');

        DB::table('carts')->where('user_id',auth()->user()->id)->delete();

        return redirect()->back()->with('info', 'Order Placed Successfully!');

    }
    public function create_client(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required',
            'address' => 'required',
            'ruc' => 'required',
            'age' => 'required',
            'category' => 'required',
            'sex' => 'required',
            'location' => 'required',
            'interest' => 'required',
            'profession' => 'required',
            'others' => 'required',


        ]);        

        $persons = $request->get('name_contact');

        foreach ($persons as $i => $person) {
            DB::table('customers')->insert([
                'name' => $request->name,
                'address' => $request->address,
                'ruc/dni' => $request->ruc,
                'bussiness_turnaround' => $request->category,
                'contact_person_name' => $persons[$i],
                'person_cellular' => $request->get('mobile_contact')[$i],
                'person_address' => $request->get('address_contact')[$i],
                'age' => $request->age,
                'sex' => $request->sex,
                'location' => $request->location,
                'product_interest' => $request->interest,
                'profession' => $request->profession,
                'other_purchase' => $request->others,


            ]);

        }

        return redirect()->back()->with('info', 'You have Registered Successfully!');
    }
    public function save_performa(Request $request)
    {
        $cart_data = DB::table('carts')->where('user_id',auth()->user()->id)->get();

        $count = sizeof($cart_data);
        foreach($cart_data as $cart)
        {
            $materials = DB::table('materials')->where('id',$cart->product_id)->first();
            DB::table('materials')->where('id',$cart->product_id)->update([
                'quantity' => $materials->quantity - $cart->quantity,
            ]);


        }
        if($count > 0){
            $cart = serialize($cart_data);
        DB::table('orders')->insert([  

            'user_id' => auth()->user()->id,
            'ruc/dni' => $request->ruc,
            'name' => $request->name,
            'address' => $request->address,
            'number' => $request->number,
            'order_data' => $cart,
        ]);
         DB::table('carts')->where('user_id',auth()->user()->id)->delete();

        return redirect()->back()->with('info', 'Saved Performa Successfully!');
        } else{
            return redirect()->back()->with('info', 'Cart Empty');

        }
        

    }

    public function searchingsale(Request $request)
    {
        $getsales = DB::table('orders')->orderby('id','desc')->get();
        $pieces = explode(',', $request->model);
        $i = 0;
        $materials[] = '';
        $quantity[] = '';
        $gross_total[] = '';
        $created_date[] = '';
        $income[] = '';
        $total = 0;
        $temp = 0;
        $exit[] = '';
        if($request->enddate < $request->startdate)
        {
            return redirect()->back()->with('info', 'End date should be greater THAN start date');

        } 
        foreach($getsales as $sales){

            if(($request->enddate >= $sales->created_at) && ($sales->created_at >= $request->startdate))
            {
                    $orders = unserialize($sales->order_data);
                    foreach($orders as $order)
                    {

                        $getmaterials = DB::table('materials')->where('id',$order->product_id)->first();
                        $numbers = DB::table('materials')->where('id',$order->product_id)->first();
                        if($temp == 0)
                        {
                            $temp = $numbers->quantity + $order->quantity;
                            $total = $total + $temp;

                        }
                        $total = $total + $order->quantity;

                        $grand = $total*$getmaterials->per_unit_price;
                        
                        if(($request->category == $getmaterials->category) && ($pieces[0] == $getmaterials->model) && ($request->name == $getmaterials->name))
                        {
                            $quantity[$i] = $order->quantity;
                            $created_date[$i] = $sales->created_at;
                            $reason[$i] = $sales->reason;
                            $income[$i] = $grand;
                            $exit[$i] = $order->quantity*$getmaterials->per_unit_price;
                            $id[$i] = $sales->id;
                            $gross_total[$i] = $order->gross_total;
                            $materials[$i] = $getmaterials;
                            $i += 1;
                        }    
                    }
            }
        }
    return view('kardex', compact('quantity','gross_total','materials','created_date','reason','id','income','exit'));

    }


    public function searchingsalecode(Request $request)
    {
        $getsales = DB::table('orders')->orderby('id','desc')->get();
        $i = 0;
        $materials[] = '';
        $quantity[] = '';
        $gross_total[] = '';
        $created_date[] = '';
        $income[] = '';
        $total = 0;
        $temp = 0;
        $exit[] = '';
        if($request->enddate < $request->startdate)
        {
            return redirect()->back()->with('info', 'End date should be greater THAN start date');

        } 
        foreach($getsales as $sales){

            if(($request->enddate >= $sales->created_at) && ($sales->created_at >= $request->startdate))
            {
                    $orders = unserialize($sales->order_data);
                    foreach($orders as $order)
                    {

                        $getmaterials = DB::table('materials')->where('id',$order->product_id)->first();
                        $numbers = DB::table('materials')->where('id',$order->product_id)->first();
                        if($temp == 0)
                        {
                            $temp = $numbers->quantity + $order->quantity;
                            $total = $total + $temp;

                        }
                        $total = $total + $order->quantity;

                        $grand = $total*$getmaterials->per_unit_price;
                        
                        if($request->code == $getmaterials->code)
                        {
                            $quantity[$i] = $order->quantity;
                            $created_date[$i] = $sales->created_at;
                            $reason[$i] = $sales->reason;
                            $income[$i] = $grand;
                            $exit[$i] = $order->quantity*$getmaterials->per_unit_price;
                            $id[$i] = $sales->id;
                            $gross_total[$i] = $order->gross_total;
                            $materials[$i] = $getmaterials;
                            $i += 1;
                        } 
                        else
                        {
                            return redirect()->back()->with('info', 'No Such Material');
                        }   
                    }
            }
            else
                        {
                            return redirect()->back()->with('info', 'No sale on these dates');
                        }  
        }
    return view('kardex', compact('quantity','gross_total','materials','created_date','reason','id','income','exit'));

    }
}
