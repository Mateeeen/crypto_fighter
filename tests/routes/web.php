<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

Route::middleware('guest')->group(function ()
{
    Route::get('/', '\App\Http\Controllers\UserController@getLogin');
    Route::get('/login', '\App\Http\Controllers\UserController@getLogin')
        ->name('login');
    Route::post('/login', '\App\Http\Controllers\UserController@postLogin');
});

Route::middleware('auth:web')->group(function ()
{

    // Route::get('/home', '\App\Http\Controllers\HomeController@getHome');
    Route::get('/user', '\App\Http\Controllers\HomeController@gettest');
    Route::post('/add_user', '\App\Http\Controllers\HomeController@add_user');
    Route::get('delete_user/{id}', '\App\Http\Controllers\HomeController@delete_user');
    Route::get('edit_user/{id}', '\App\Http\Controllers\HomeController@edit_user');
    Route::post('/save_changes', '\App\Http\Controllers\HomeController@update');

    Route::get('/home', function ()
    {
        $times = DB::table('times')->first();
        return view('home', compact('times'));
    });
    Route::get('/profile/{id}', function ($id)
    {
        $employee = DB::table('employees')->where('id', $id)->first();
        return view('profile', compact('employee'));
    });
    Route::get('/project_list', function ()
    {
        
        return view('project_list');
    });
    Route::get('/production', function ()
    {
        return view('production');
    });
    Route::get('/dedicated_time', function ()
    {
        return view('dedicated_time');
    });
    Route::get('/history_employees', function ()
    {
        return view('history_employees');
    });
    Route::get('/new_employee', function ()
    {
        return view('new_employee');
    });
    Route::get('/create_state', function ()
    {
        return view('create_state');
    });
    Route::get('/list_tools', function ()
    {
        return view('list_tools');
    });
    Route::get('/list_employees', '\App\Http\Controllers\HomeController@getAllEmployees');

    Route::get('/create_tool', function ()
    {
        return view('create_tool');
    });
    Route::get('/create_project', function ()
    {
        $tasks = DB::table('tasks_and_subtasks')->get();

        return view('create_project', compact('tasks'));
    });
    Route::post('/create_project', '\App\Http\Controllers\HomeController@postAddProject');

    Route::get('/view_project', function ()
    {

        $projects = DB::table('projects')->orderby('id', 'desc')
            ->get();
        $tasks = DB::table('tasks')->get();

        return view('project_list', compact('projects','tasks'));
    });
   Route::get('/search_project', function ()
    {

        $projects = DB::table('projects')->orderby('id', 'desc')->get();
        foreach($projects as $row){
            $workers = DB::table('daily_work_performance')->where('project_id',$row->id)->get();
            $labour = null;
            if(isset($workers)){
            foreach($workers as $value){
                $labour += DB::table('employees')->where('id',$value->employee_id)->pluck('salary')->first() * $value->working_time;
            }
            $recent_task = DB::table('project_task')->where('project_id',$row->id)->pluck('task_name')->first();
             $total_tasks = DB::table('daily_work_performance')->where('project_id',$row->id)->count();
            $row->progress = $row->progress = DB::table('daily_work_performance')->where('project_id',$row->id)->sum('working_time') / DB::table('project_operator')->where('project_id',$row->id)->sum('total_hour') * 100;
            $row->labour_cost = $labour;
            $row->current_task = $recent_task;
        }
            
        }
        

        return view('search_project', compact("projects"));
    });
    Route::post('/search_project', '\App\Http\Controllers\HomeController@SearchProject');
    

    Route::post('/change_status', '\App\Http\Controllers\HomeController@postchangeStatus');

    Route::get('/assign_task', function ()
    {
        $projects = DB::table('projects')->where('status',1)->orderby('project','asc')->get();
        return view('assign_task',compact('projects'));
    });

    Route::post('/assign_task', '\App\Http\Controllers\HomeController@postAssignTask');

    Route::get('/tasks-subtasks', function ()
    {
        $tasks = DB::table('tasks_and_subtasks')->where('parent_id', 0)
            ->get();

        return view('create_tasks_and_subtasks', compact('tasks'));
    });

    Route::post('/create_task', '\App\Http\Controllers\HomeController@postCreateTask');
    Route::post('/create_subtask', '\App\Http\Controllers\HomeController@postCreateSubTask');
    Route::get('/schedule-task-subtask', function ()
    {
        $tasks = DB::table('tasks')->orderBy('id', 'DESC')
            ->get();

        $subtasks = DB::table('sub_tasks')->orderBy('id', 'DESC')
            ->get();
        return view('schedule_tasks_subtasks', compact('tasks', 'subtasks'));
    });
    Route::get('/add-fabrication', function ()
    {
        return view('create_fabrication');
    });

    Route::get('/view-materials', function ()
    {
        $materials = DB::table('materials')->orderBy('id', 'DESC')
            ->get();
        return view('view_materials', compact('materials'));
    });

    Route::get('/delete/{id}', function ($id)
    {

        DB::table('materials')->where('id', $id)->delete();
        return redirect()
            ->back()
            ->with('info', 'You have Deleted Successfully!');
    });

    Route::get('/register-materials', function ()
    {

        return view('register_materials');
    });
    Route::post('/register_material', '\App\Http\Controllers\HomeController@postRegisterMaterials');

    Route::post('/create_fabrication', '\App\Http\Controllers\HomeController@postAddfabrication');

    Route::get('/password', '\App\Http\Controllers\HomeController@getChangePassword');
    Route::post('/password', '\App\Http\Controllers\HomeController@postChangePassword');

    Route::post('/add_emloyee', '\App\Http\Controllers\HomeController@postAddEmployee');
    Route::post('/add_time', '\App\Http\Controllers\HomeController@postAddtime');

    Route::get('/logout', '\App\Http\Controllers\UserController@getLogout');

    Route::post('/change_actual_cost', function (Request $request)
    {
        DB::table('projects')->where('id', $request->id)
            ->update(['actual_cost' => $request->actual_cost, ]);

        return redirect()
            ->back()
            ->with('info', 'Successfully Updated');
    });

    Route::post('/change_current_status', function (Request $request)
    {
        DB::table('projects')->where('id', $request->id)
            ->update(['current_task' => $request->current_task, ]);

        return redirect()
            ->back()
            ->with('info', 'Successfully Updated');
    });

    Route::post('/change_degree_of_progree', function (Request $request)
    {

        if ($request->degree_of_progress > 100 || $request->degree_of_progress < 0)
        {
            return redirect()
                ->back()
                ->with('alert', 'Degree of Progress must be percentage');
        }

        DB::table('projects')
            ->where('id', $request->id)
            ->update(['degree_of_progress' => $request->degree_of_progress, ]);

        return redirect()
            ->back()
            ->with('info', 'Successfully Updated');
    });

    Route::get('/daily_worker_performance', '\App\Http\Controllers\HomeController@daily_worker_performance');
    Route::post('/daily_worker_performance', '\App\Http\Controllers\HomeController@post_daily_worker_performance');

    Route::get('/work-performance', function ()
    {   

       // To cenvert months into date
        $now = Carbon::now();
        $date[0] = $now->year;
        $date[1] = $now->month;
        $date2 = $now->year.'-'. Carbon::now()->format('m');;

        
        $tasks_id = DB::table('daily_work_performance')->whereMonth('date',$date[1])->get()->pluck('task_id');
        $tasks = DB::table('project_task')->whereIn('id',$tasks_id)->get();
        $projects = DB::table('projects')->get();

        
            return view('performance_of_work', compact('tasks','projects','date2','date'));
        

    });

    Route::post('/work-performance', '\App\Http\Controllers\HomeController@Search');


    Route::post('get_projects_ajax', function(Request $request)
    {

        // $employee =  DB::Table('employees')->where('name',$request->name)->pluck('id')->first();
        $employee_id =  DB::Table('assignments')->where('employee_id_1',$request->name)->get()->pluck('project_id');
            $projects = DB::table('projects')->whereIn('id',$employee_id)->get();
        
        $data = "<option>Select Project</option>";
        foreach ($projects as $key => $row) {
            $data .= "<option value=".$row->id.">".$row->project."</option>";
        }

        if(count($projects) == 0){
            $data = null;
        }

        return $data;   
    });

    Route::post('get_tasks_ajax', function(Request $request)
    {
        $tasks = DB::table('project_task')->where('project_id',$request->project)->get();

        
        $data = null;
        foreach ($tasks as $key => $row) {
            $data .= "<option value=".$row->id.">".$row->task_name."</option>";
        }
        return $data;   
    });

    Route::post('save_tasks', function(Request $request)
    {
        if ($request->file('filer')) {
            $file = $request->file('filer');
            $filename = $file->getClientOriginalName();
            $path = public_path() . '/tasks';
            $file->move($path, $filename);
        dd($filename);
        DB::table('project_task')->insert([

            'project_id' => 0,
            'task_number' => 0,
            'file'=>$filename,
            'task_name' => $request->nombre,
            'target_quantity' => $request->cantidad,
            'location' => $request->ubicación,
            'directions' => $request->indicaciones,


        ]);

        
    }else {
        if(isset($request->nombre)){
            
        DB::table('project_task')->insert([

            'project_id' => 0,
            'task_number' => 0,
            'task_name' => $request->nombre,
            'location' => $request->ubicación,


        ]);
        $data = "Added Successfully";
    } else {
        $data = 1;  
    }
    if(isset($request->type)){
        DB::table('project_operator')->insert([

            'project_id' => 0,
            'task_id' => 0,
            'operator_type' => $request->type,
            'number_of_operator' => $request->operators,


        ]);
    }
    if(isset($request->material_name)){
        DB::table('project_material')->insert([

            'project_id' => 0,
            'task_id' => 0,
            'material_name' => $request->material_name,
        ]);
    }
    if(isset($request->tool_name)){
        DB::table('project_tool')->insert([

            'project_id' => 0,
            'task_id' => 0,
            'tool_name' => $request->tool_name,
        ]);
    }
    }
        return $data;   
    });

    Route::post('get_task_list', function(Request $request)
    {
        $tasks = DB::table('project_task')->where('project_id',$request->project)->get();
       
        $data = null;
        foreach ($tasks as $key => $row) {
            $data .= "<option value=".$row->id.">".$row->task_name."</option>";
        }
        return $data;   
    });

    Route::get('task_complete_{id}',function($id){
        DB::Table('daily_work_performance')->where('id',$id)->update([
                'status' => 1,
        ]);

        return redirect()->back()->with('info','Successfully Updated');
    });

    Route::get('task_reopen_{id}',function($id){
        DB::Table('daily_work_performance')->where('id',$id)->update([
                'status' => 0,
        ]);

        return redirect()->back()->with('info','Successfully Updated');
    });
    Route::post('get_models_ajax', function(Request $request)
    {

        switch($request->date)
        {
                case 'Servicios Metalmecánic':
                    
                    $data = '<option value="Corte de planchas,0101">Corte de planchas</option> 
                    <option value="Corte y doblez de planchas,0102">Corte y doblez de planchas</option>
                    <option value="Rolado de tubos,0103">Rolado de tubos</option>
                    <option value="Doblado de tubos,0104">Doblado de tubos</option>
                    <option value="Torno,0105">Torno</option>
                    <option value="Pintura al horno,0106">Pintura al horno</option>
                    <option value="Troquelado,0107">Troquelado</option>';
                    break;
                case 'Tubos, Perfiles y vigas':
                    $data = '<option value="Tubos Galvanizados,0201">Tubos Galvanizados</option>
                    <option value="Tubos Negros,0202">Tubos Negros
                    </option>
                    <option value="Vigas,0203">Vigas</option>
                    <option value="Ángulos,0204">Ángulos</option>
                    <option value="Platinas,0205">Platinas</option>
                    <option value="Rieles,0206">Rieles</option>';
                    break;
                case 'Planchas':
                    $data ='<option value="Galvanizada,0301">Galvanizada</option>
                    <option value="Negra,0302">Negra</option>
                    <option value="Estriada,0303">Estriada</option>
                    <option value="Inoxidables,0304">Inoxidables</option>';
                    break;
                case 'Retazos':
                    $data ='<option value="Plancha Galvanizada,0401">Plancha Galvanizada</option>
                    <option value="Plancha Inoxidable,0402">Plancha Inoxidable</option>
                    <option value="Plancha Negra,0403">Plancha Negra</option>
                    <option value="Plancha Estriada,0404">Plancha Estriada</option>
                    <option value="Tubos inoxidables,0405">Tubos inoxidables</option>
                    <option value="Tubos y perfiles,0406">Tubos y perfiles</option>';
                    break;
                case 'Coberturas':
                    $data = '<option value="Aluzinc,0501">Aluzinc</option>
                    <option value="Eternit,0502">Eternit</option>';
                    break;
                case 'Fabricaciones':
                    $data = '<option value="Terminadas,0601">Terminadas</option>
                    <option value="En proceso,0602">En proceso
                    </option>';
                    break;
                case 'Prefabricados':
                    $data = '<option value="Marcos para puerta,0701">Marcos para puerta</option>
                    <option value="Arcos/barandas de tubo negroArcos/barandas de tubo negro,0702">Arcos/barandas de tubo negro</option>
                    <option value="Arcos/barandas de tubo inoxidable,0703">Arcos/barandas de tubo inoxidable</option>
                    <option value="Tapas de plancha,0704">Tapas de plancha</option>
                    <option value="Cajas,0705">Cajas</option>
                    <option value="Chasis de moto,0706">Chasis de moto</option>';
                    break;
                case 'Pernos y tornillos':
                    $data = '<option value="Pernos,0801">Pernos</option>
                    <option value="Tornillos,0802">Tornillos</option>
                    <option value="Autoperforantes,0803">Autoperforantes</option>';
                    break;
                case 'Ferretería - Soldad':
                    $data ='<option value="Discos,0901">Discos</option>
                    <option value="Soldadura,0902">Soldadura</option>
                    <option value="Acabados,0903">Acabados</option>
                    <option value="Gases,0904">Gases</option>';
                    break;
                case 'Ferretería - Inoxidabl':
                    $data = ' <option value="Discos,0111">Discos</option>
                    <option value="Soldadura,0112">Soldadura</option>
                    <option value="Acabados,0113">Acabados</option>
                    <option value="Gases,0114">Gases</option>
                    ';
                    break;
                case 'Ferretería - Uso común':
                    break;
                case 'Ferretería - Made':
                    break;
                case 'Ferretería - Drywall':
                    break;
                case 'Ferretería - Albañile':
                    break;
                case 'Ferretería - Electricistas':
                    break;
        }
        

        return $data;   
    });

    Route::post('get_names_ajax', function(Request $request)
    {
        $pieces = explode(',', $request->model);
        $names = DB::table('materials')->where('category',$request->category)->where('model',$pieces[0])->get();
        $data = "<option>Select Material</option>";
        if(count($names) > 0)
        {
        foreach ($names as $name) {
            $data .= "<option value=".$name->id.">".$name->name."</option>";
        };
    };
        
        if(count($names) == 0){
            $data = null;
        };

        return $data;   
    });
    Route::post('get_name_ajax', function(Request $request)
    {
        $pieces = explode(',', $request->model);
        $names = DB::table('materials')->where('category',$request->category)->where('model',$pieces[0])->get();
        $data = "<option>Select Material</option>";
        if(count($names) > 0)
        {
        foreach ($names as $name) {
            $data .= "<option value=".$name->name.">".$name->name."</option>";
        };
    };
        
        if(count($names) == 0){
            $data = null;
        };
        return $data;   
    });


    Route::post('get_cart_ajax', function(Request $request)
    
    {
        
        
        $int = (int)$request->quantity;
        $price = DB::table('materials')->where('id',$request->id)->first();
        $check_cart = DB::table('carts')->where('product_id',$request->id)->first();
        $gross_total = $int*$price->per_unit_price;
        if(isset($check_cart)){
            DB::table('carts')->where('product_id',$request->id)->update([

                
                'quantity'=>$int + $check_cart->quantity,  
                'gross_total'=>$gross_total + $check_cart->gross_total,  
            ]);
        } else{
            DB::table('carts')->insert([

                'user_id' => auth()->user()->id,
                'product_id' => $request->id,
                'per_unit_price'=>$price->per_unit_price,  
                'quantity'=>$int,  
                'gross_total'=>$gross_total,  
    
    
    
            ]);
        }

        
        $loop="";
        $gross_total = 0;
        $amount = 0;
        $cart_data = DB::table('carts')->where('user_id',auth()->user()->id)->get();
        if(count($cart_data) > 0)
        {
            foreach ($cart_data as $cart) {
                $name = DB::table("materials")->where("id",$cart->product_id)->first();
                $loop .= 
                ' 
                <tr>
                
                <td>'.$name->code.'</td>
                <td>'.$name->name.'</td>
                <td>'.$cart->per_unit_price.'</td>
                <td>'.$cart->quantity.'</td>
                <td>'.$cart->gross_total.'</td>


                <td>
                    <a class="removecart" href="remove_cart/'.$cart->id.'"><i
                    class=" bx bxs-minus-circle"></i></a>
                </td>                
                
                </tr>
                
                ';

                $amount += $cart->gross_total;
            };
            $data = $loop.'<tr><td></td><td></td><td></td><td></td><td class="font-weight-bold text-right">Total Amount = $'.$amount.'</td></tr> ';
        };
        
        if(count($cart_data) == 0){
            $data = null;
        };
       
        
        
        return $data;
         
    });

    Route::get('/remove_cart/{id}', function($id)
    
    {
        DB::table('carts')->where('id',$id)->delete();
        $data = "Successfully Removed";
        return redirect()->back()->with('info', 'Removed Item Successfully!');

         
    });

    Route::post('get_search_ajax', function(Request $request)
    
    {  
        
        $data = 2;
        $pieces = explode(',', $request->model);
        
        $material = DB::table('materials')->where('category',$request->category)->where('model',$pieces[0])->where('name',$request->name)->first();
        if($material->quantity == 0){
            $data = "Sold Out";
            return $data;
        }
        if(isset($material)) {
            $alph =
                                                "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
                                                $quantity='';
                                                $lsymbol='';
                                                $type= '';

                                                for($i=0;$i < 7;$i++){ $quantity .=$alph[rand(0, 55)]; } for($i=0;$i <
                                                    7;$i++){ $lsymbol .=$alph[rand(0, 55)]; } for($i=0;$i < 7;$i++){
                                                    $type .=$alph[rand(0, 55)]; }
            $data = 
            ' 
            <tr>
            <input type="hidden" id="limit" value='.$material->quantity.'>

            <td> <span id="'.$quantity.'">
            <span 
                                                                onclick="remove(`'.$quantity.'`,`'.$lsymbol.'`,`'.$type.'`)">
                                                                <i class=" bx bxs-minus-circle"></i>
                                                            </span>
            1
            <input type="hidden" id="'.$type.'" value="1">
            <span id="'.$lsymbol.'"
                onclick="add(`'.$quantity.'`,`'.$lsymbol.'`,`'.$type.'`)">
                <i class=" bx bxs-plus-circle"></i>
            </span>
        </span></td>
            <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->code.'</td>
            <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->name.'</td>
            <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->category.'</td>
            <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->model.'</td>
            <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->per_unit_price.'</td>

            <td>
            <input type="hidden" id="'.$material->id.'" value ="'.$material->id.'">
            </td>
            
            
            </tr>
            
            ';
        };
        return $data;
         
    });


    


    Route::post('get_searchcode_ajax', function(Request $request)
    
    {  
        $data = 2;
        
        $material = DB::table('materials')->where('code',$request->code)->first();
        if($material->quantity == 0){
            $data = "Sold Out";
            return $data;
        }
        if(isset($material)) {
            $alph =
                                                "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
                                                $quantity='';
                                                $lsymbol='';
                                                $type= '';

                                                for($i=0;$i < 7;$i++){ $quantity .=$alph[rand(0, 55)]; } for($i=0;$i <
                                                    7;$i++){ $lsymbol .=$alph[rand(0, 55)]; } for($i=0;$i < 7;$i++){
                                                    $type .=$alph[rand(0, 55)]; }
            $data = 
            ' 
            <tr>
            <input type="hidden" id="limit" value='.$material->quantity.'>

            <td>  <span id="'.$quantity.'">
            <span 
                                                                onclick="remove(`'.$quantity.'`,`'.$lsymbol.'`,`'.$type.'`)">
                                                                <i class=" bx bxs-minus-circle"></i>
                                                            </span>
            1
            <input type="hidden" id="'.$type.'" value=1>

            <span id="'.$lsymbol.'"
                onclick="add(`'.$quantity.'`,`'.$lsymbol.'`,`'.$type.'`)">
                <i class=" bx bxs-plus-circle"></i>
            </span>
        </span>
        </td>
        <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->code.'</td>
        <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->name.'</td>
        <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->category.'</td>
        <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->model.'</td>
        <td  style="cursor: pointer;" onclick="myFunction('.$material->id.',`'.$type.'`)"class="addcart">'.$material->per_unit_price.'</td>

            <td>
            <input type="hidden" id="'.$material->id.'" value ="'.$material->id.'">
            </td>
            
            
            </tr>
            
            ';
        };
        return $data;
         
    });




    Route::post('get_searchstocks_ajax', function(Request $request)
    
    {  
        $data = 2;
        $pieces = explode(',', $request->model);
        
        $material = DB::table('materials')->where('category',$request->category)->where('model',$pieces[0])->where('name',$request->name)->first();
        if(isset($material)) {
            
            
            $data = 
            ' 
            
            <tr>
            <td>'.$material->code.'</td>
            <td>'.$material->name.'</td>
            <td>'.$material->category.'</td>
            <td>'.$material->model.'</td>
            <td>$'.$material->per_unit_price.'</td>
            <td>'.$material->quantity.'</td> 
            <td>$'.$material->quantity*$material->per_unit_price.'</td>          
         

            </tr>
            
            
            ';
        };
        return $data;
         
    });

    Route::post('get_searchcodestocks_ajax', function(Request $request)
    
    {  
        $data = 2;
        
        $material = DB::table('materials')->where('code',$request->code)->first();
        if(isset($material)) {
            
            $data = 
            ' 
            <tr>
            <td>'.$material->code.'</td>
            <td>'.$material->name.'</td>
            <td>'.$material->category.'</td>
            <td>'.$material->model.'</td>
            <td>$'.$material->per_unit_price.'</td>  
            <td>'.$material->quantity.'</td> 
            <td>$'.$material->quantity*$material->per_unit_price.'</td>                   
            </tr>
            
            ';
        };
        return $data;
         
    });

    Route::post('/update_materialas', function(Request $request)
    {
        $old_quantity = DB::table('materials')->where('id',$request->name)->first();

        DB::table('materials')->where('id',$request->name)->update([
            'quantity' => $request->number + $old_quantity->quantity,
            'per_unit_price' => $request->price,
              
        ]);
        return redirect()->back()->with('info', 'You have Entered Materials Stock Successfully!');

    });

    Route::get('/Change-Status/{id}', '\App\Http\Controllers\HomeController@Change_status');
    Route::get('/Change-Statuss/{id}', '\App\Http\Controllers\HomeController@Change_statuss');


    Route::get('/material-edit',function(){
        return view('material_edit');
    });
    Route::get('/material-stock',function(){
        $materials =DB::table('materials')->get();
        return view('material_stock',compact('materials'));
    });
    Route::get('/create-materialas',function(){
        return view('create_materialas');
    });
    Route::post('/create_materialas', '\App\Http\Controllers\HomeController@create_materialas');
    Route::post('/getpdf', '\App\Http\Controllers\HomeController@getpdf');

    Route::get('/materialas-cart',function(){
        $amount = 0;
        $carts = DB::table('carts')->where('user_id',auth()->user()->id)->get();
        
        foreach($carts as $cart){
            $amount += $cart->per_unit_price;
        }
        $materials = DB::table('materials')->get();
        return view('material_cart',compact('materials','carts','amount'));
    });
    Route::get('/add-services', function ()
    {
        

        return view('services');
    });
    Route::post('/create_service', '\App\Http\Controllers\HomeController@create_service');
    Route::get('/add-client', function ()
    {
        

        return view('create_client');
    });

    Route::get('/from', function ()
    {
        return view('fromm');
    });
    Route::post('/create_client', '\App\Http\Controllers\HomeController@create_client');
    Route::post('/save_performa', '\App\Http\Controllers\HomeController@save_performa');

    Route::get('/kardex', function ()
    {
        

        return view('kardex');
    });
    Route::post('/searchingsale', '\App\Http\Controllers\HomeController@searchingsale');
    Route::get('/searchingsale', function ()
    {
        

        return view('kardex');
    });
    Route::post('/searchingsalecode', '\App\Http\Controllers\HomeController@searchingsalecode');
    Route::get('/searchingsalecode', function ()
    {
        

        return view('kardex');
    });


    

});



