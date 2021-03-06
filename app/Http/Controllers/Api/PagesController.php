<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Calendar;
use DB;  
use App\Model\Room;
use App\Model\Role;
use App\Model\Accounts;
use App\Model\Device;
use App\Model\Data_Reading;
use App\CustomModel\wattsAndPower;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr; 
class PagesController extends Controller
{
    //
    public function layouts(){
        // $title = 'Welcome to our homepage!';
        return view('layouts.index');
        // return view('pages.index', compact('title'));
        // return view('layouts.index')->with('title', $title);
    }

    public function dashboard(){
        // $data = array(
        //     'title' => 'dashboard',
        //     'service' => ['Web design', 'Programming', 'SEO']
        // ); 
        // return view('pages.dashboard')->with($data);
        return view('pages.dashboard');
    }
    
    public function summary(){ 
        $data = Room::all();
        $role = Role::all();
        $roomAvailability = Room::where('status','=',true)->get(); 
        $users = Accounts::all();
        $Device = Device::all();
        // $data_reading = Data_Reading::paginate(100); 

        // $setDateFrom = '2019-09-1 08:07:22';
        // $setDateTo = Carbon::parse()->startOfDay()->toDateTimeString(); 
 
        return view('pages.summary')->with([
            'rooms'=> $data,
            'role'=> $role,
            'users'=> $users,
            'roomAvailability' => $roomAvailability, 
            'Device'=> $Device
            ]); 
    } 

    public function testforProfile(){
        $data =Room::all()->where('status','=',true);
        $getID = Auth::user()->id;
        $item = DB::table('users')->where('id','=',$getID)->where('isDeleted','=',0);
        return view('pages.accounts.profile', compact('item'))->with('rooms',$data);
    }

    public function index(){
        
        $rooms  = Room::take()->get();
        return view();
    }

    public function showDashboard(Request $request)
    {
        $getDate1 =  $request->get('datepicker') != null ||  $request->get('datepicker') != '' ? $request->get('datepicker'): '2019-09-1 08:07:22';
        $getDate2 =  $request->get('datepicker2') != null ||  $request->get('datepicker2') != '' ? $request->get('datepicker2'): '2019-09-1 08:07:22';

        $from = Carbon::parse($getDate1)
        ->startOfDay()        // 2018-09-29 00:00:00.000000
        ->toDateTimeString(); // 2018-09-29 00:00:00

        $to = Carbon::parse($getDate2)
        ->endOfDay()          // 2018-09-29 23:59:59.000000
        ->toDateTimeString(); // 2018-09-29 23:59:59

        $data  = Room::whereBetween('created_at', [$from, $to])->get();
        $roomAvailability  = Room::whereBetween('created_at', [$from, $to])->where('status','=',true)->get(); // status = 1 equivalent to true or available
        $role = Role::whereBetween('created_at', [$from, $to])->get();
        $users = Accounts::whereBetween('created_at', [$from, $to])->get();
        $Device = Device::whereBetween('created_at', [$from, $to])->get();
        $data_reading = Data_Reading::whereBetween('created_at', [$from, $to])->get();  
        // $this->getDataForDashboard($request);
        // $this->getGuageForDashboard($request);
        return view('pages.summary')->with([
            'rooms'=> $data,
            'roomAvailability' => $roomAvailability,
            'role'=> $role,
            'users'=> $users,
            'Device'=> $Device,
            'dataReading'=> $data_reading 
            ]);
    }

    public function getDataForDashboard_Post(Request $request)
    {  
        $data = new Collection();
        $daysOnly = new Collection();
        $daysAndHours = new Collection();
        $getTotal_by_room = new Collection();   
        $getWatts = new wattsAndPower();  

        $DateFrom = $this->DateFrom(
           $request->dateFrom != null ||  $request->dateFrom != '' ? $request->dateFrom: '2019-09-1 08:07:22'
        );
        $DateTo = $this->DateFrom(
            $request->dateTo != null ||  $request->dateTo != '' ? $request->dateTo: now()
        );
        
        if($request->room > 0){ 
            $data =  $this->getSelectRoom(
                $request->room
            ); 

            $daysAndHours = DB::table('data_reading')  
            ->selectRaw('data_reading.*,created_at')
            ->where('room_ID','=', Arr::pluck($data, 'id'))
            ->whereBetween('created_at', [$DateFrom, $DateTo])->get()
            ->groupBy(function ($val) {
              return Carbon::parse($val->created_at)->format('d/h');//d is for day// i change to h is define to hour
            });

            $daysOnly = DB::table('data_reading')  
            ->selectRaw('data_reading.*,created_at')
            ->where('room_ID','=',Arr::pluck($data, 'id'))
            ->whereBetween('created_at', [$DateFrom, $DateTo])->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('d');//d is for day// i change to h is define to hour
            });
        
            $getTotal_by_room = DB::table('data_reading')->where('room_ID','=',Arr::pluck($data, 'id'))
            ->selectRaw('data_reading.power,created_at')
            ->whereBetween('created_at', [$DateFrom, $DateTo])->sum('power');
        }

         else{
                $daysAndHours = DB::table('data_reading')  
                ->selectRaw('data_reading.*,created_at') 
                ->whereBetween('created_at', [$DateFrom, $DateTo])->get()
                ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('d/h');//d is for day// i change to h is define to hour
                });

                $daysOnly = DB::table('data_reading')  
                ->selectRaw('data_reading.*,created_at') 
                ->whereBetween('created_at', [$DateFrom, $DateTo])->get()
                ->groupBy(function ($val) {
                return Carbon::parse($val->created_at)->format('d');//d is for day// i change to h is define to hour
                });

                $getTotal_by_room = DB::table('data_reading')
                ->selectRaw('data_reading.power,created_at') 
                ->whereBetween('created_at', [$DateFrom, $DateTo])->sum('power');
             }
            //Start Display for Line Type (HOURLY)
            $getFilterDataDaysAndHours = [];
            foreach($daysAndHours as $key => $daysItem)
            {  
                      $getFilterDataDaysAndHours[]= $daysItem->sum('power')/count(Arr::pluck($daysItem, '0.power'));
            } 
            // show record to console

            $getWatts->TotalWatts =(((($getTotal_by_room/60)/24)/1000)*count(Arr::pluck($daysOnly, '0.created_at')));  // complete from 1 to until now  
            $getWatts->TotalVoltage = 220; 
            $getWatts->showCount = count(Arr::pluck($daysAndHours, '0.created_at'));
            $getWatts->parseTotalVoltage = $getFilterDataDaysAndHours;
            $getWatts->daysDataAndHours = $daysAndHours;  
            $getWatts->collectionDate =Arr::pluck($daysAndHours, '0.created_at');
             //Start Display for Bar Type    (DAILY)
            $getFilterDataDaysOnly = [];
            foreach($daysOnly as $key => $item)
            {
                $getFilterDataDaysOnly[] = ((($item->sum('power'))/60)/24)/1000;
            }
            $getWatts->getDataforBarChart =$getFilterDataDaysOnly;   
            $getWatts->collectionDate_BarType =  Arr::pluck($daysOnly, '0.created_at'); 
            $getWatts->barChartTotalSum = array_sum($getFilterDataDaysOnly);
            // $getWatts->getRoomData = $collectionSample; 
            return response()->json($getWatts); 
    }
    
    public function getDataForDashboard()
    {  
        $collectionSample = new Collection(); 
        $collectionByDaysAndHours = new Collection();  
        $collectionByDaysOnly = new Collection();  
        $getData = new Collection();   
        $getWatts = new wattsAndPower();  
        $getFilterDataDaysAndHoursData = new Collection();
        $collectionByDaysOnlyData =new Collection;

        
        $collectionSample = Room::where('id','>',0)->get(); 
        $getData = DB::table('data_reading')->where('power', '<', 800)->paginate(50);
        
        $collectionByDaysOnly = $getData->groupBy(function ($val) {
            return Carbon::parse($val->created_at)->format('d');
         }); 
         $collectionByDaysAndHours = $getData->groupBy(function($val1){
            return Carbon::parse($val1->created_at)->format('d/h');
         });

        //Start Display for Line Type (HOURLY)
        $getFilterDataDaysAndHoursData = $collectionByDaysAndHours->map(function ($dHours, $key) {
            return (($dHours->avg('power'))); //divide to $dHours->count()
        }); 
        //Start Display for Bar Type    (DAILY)
        // $collectionByDaysOnlyData = $collectionByDaysOnly->map(function ($user) {
        //     return ((($user->sum('power')/60)/24)/1000);
        // }); 
        //End Display for Bar Type
 
        // show record to console     
        $getWatts->TotalWatts =(((($getData->sum('power')/60)/24)/1000)*count(Arr::pluck($collectionByDaysOnly, '0.created_at')));  // complete from 1 to until now  
        $getWatts->TotalVoltage = 220; 
        $getWatts->parseTotalVoltage = $getFilterDataDaysAndHoursData;
        $getWatts->collectionDate =Arr::pluck($collectionByDaysAndHours, '0.created_at');
        $getWatts->getDataforBarChart =$collectionByDaysOnly->map(function ($user) {
            return ((($user->sum('power')/60)/24)/1000);
        }); 
        $getWatts->collectionDate_BarType = Arr::pluck($collectionByDaysOnly, '0.created_at'); 
        $getWatts->getRoomData = $collectionSample; 
        return response()->json($getWatts);  
    }
    private function take($collection, $limit)
    {
        return array_slice($collection->toArray(), $limit, abs($limit));
    }
    private function DateFrom($dataFrom){
        return Carbon::parse($dataFrom)
        ->startOfDay()        // 2018-09-29 00:00:00.000000
        ->toDateTimeString(); // 2018-09-29 00:00:00
    }
    private function DateTo($dataTo){
        return Carbon::parse($dataTo)
        ->endOfDay()          // 2018-09-29 23:59:59.000000
        ->toDateTimeString(); // 2018-09-29 23:59:59
    }

    private function getSelectRoom($getData)
    {
       $id = $getData != null || $getData != '0'? $getData: 0;
        
        if($id > 0)
            {  
            return Room::where('id','=',$id)
            ->orderBy('rooms.created_at','desc')
            ->get();
            }
        
        else
        
            return Room::where('id','>',0)   
            ->get();
    }
    
 
}