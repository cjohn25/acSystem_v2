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
use Illuminate\Support\Collection;use Illuminate\Support\Arr;
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
        $roomAvailability  = Room::where('status','=',true)->get(); 
        $users = Accounts::all();
        $Device = Device::all();
        $data_reading = Data_Reading::all();

        $setDateFrom = '2019-09-1 08:07:22';
        $setDateTo = Carbon::parse()->startOfDay()->toDateTimeString(); 

        return view('pages.summary')->with([
            'rooms'=> $data,
            'role'=> $role,
            'users'=> $users,
            'roomAvailability' => $roomAvailability,
            'Device'=> $Device,
            'dataReading'=> $data_reading 
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
        $DateFrom = $this->DateFrom(
            $request->get('datepicker') != null ||  $request->get('datepicker') != '' ? $request->get('datepicker'): '2019-09-1 08:07:22'
        );
        $DateTo = $this->DateFrom(
            $request->get('datepicker2') != null ||  $request->get('datepicker2') != '' ? $request->get('datepicker2'): now()
        );
        $data =  $this->getSelectRoom(
            $request->get('selectRoom')
        ); 

        $allRoom = Room::where('id','>',0)   
        ->orderBy('rooms.created_at','desc')
        ->get();  

        $daysAndHours = DB::table('data_reading')  
                ->selectRaw('data_reading.*,created_at')
                ->where('room_ID','=',$request->get('selectRoom'))
                ->whereBetween('created_at', [$DateFrom, $DateTo])->get()
                ->groupBy(function ($val) {
                  return Carbon::parse($val->created_at)->format('d/h');//d is for day// i change to h is define to hour
                });
         $daysOnly = DB::table('data_reading')  
                ->selectRaw('data_reading.*,created_at')
                ->where('room_ID','=',$request->get('selectRoom'))
                ->whereBetween('created_at', [$DateFrom, $DateTo])->get()
                ->groupBy(function ($val) {
                  return Carbon::parse($val->created_at)->format('d');//d is for day// i change to h is define to hour
                });

        $getWatts = new wattsAndPower();
   
        // $getWatts->parseTotalVoltage = $days->sum('power')/1000;
        // $getWatts->parseTotalVoltage = Arr::pluck($days, count($days));
        // $getYou =  Arr::pluck($days,  count($days)); 
        // $getWatts->getYou = $getYou;
        
        // $getLengthofDays =  count(Arr::pluck($daysAndHours, '0'));  
         
        //Start Display for Line Type 
            $getFilterDataDaysAndHours = []; 
            foreach($daysAndHours as $key => $daysItem)
            {  
                    $getFilterDataDaysAndHours[]= $daysItem->sum('power'); 
            } 
                //Start Overall Total
                $getWatts->TotalWatts = array_sum($getFilterDataDaysAndHours)/1000;   // complete from 1 to until now  
                $getWatts->TotalVoltage = 220;      
                //End Overall Total without parsing data  
            $getWatts->parseTotalVoltage =  $getFilterDataDaysAndHours;
            $getWatts->daysDataAndHours = $daysAndHours;
            $getWatts->collectionDate =Arr::pluck($daysAndHours, '0.created_at');
        //End Display for Line Type 
 



        //Start Display for Bar Type
        
        $getFilterDataDaysOnly = [];
        foreach($daysOnly as $key => $item)
        {
            $getFilterDataDaysOnly[] = $item->sum('power');
        }
        $getWatts->getDataforBarChart =$getFilterDataDaysOnly;
        $getWatts->collectionDate_BarType = Arr::pluck($daysOnly, '0.created_at'); 
        $getWatts->getRoomData = $allRoom; 
        //End Display for Bar Type

        return response()->json($getWatts); 
    }
    public function getDataForDashboard()
    { 
        
        $collectionSample = new Collection(); 
        $collectionByDaysAndHours = new Collection();  
        $collectionByDaysOnly = new Collection();  
        $collectionSample = Room::where('id','>',0)->get();  
        $RoomDataCollection = new Collection(); 
        $RoomDataCollection = Arr::pluck($collectionSample, 'id'); 
        $getData =Data_reading::where('room_ID','=',$RoomDataCollection)->orderBy('data_reading.created_at','desc')->get();  

        $getTotalPowerFromGETDATA =$getData->sum('power'); 
        $getTotalVoltageFromGETDATA = $getData->sum('voltage');

        //Days and Hours
        $collectionByDaysAndHours = DB::table('data_reading')
        ->selectRaw('data_reading.*,created_at') 
        ->whereRaw('created_at between DATE_SUB("2019-1-1 08:07:22", INTERVAL 15 MINUTE) and NOW()')->get() 
        ->groupBy(function ($val) {
           return Carbon::parse($val->created_at)->format('d/h');//d is for day// i change to h is define to hour
        });
          
        //Days only
        $collectionByDaysOnly = DB::table('data_reading')
        ->selectRaw('data_reading.*,created_at') 
        ->whereRaw('created_at between DATE_SUB("2019-1-1 08:07:22", INTERVAL 15 MINUTE) and NOW()')->get() 
        ->groupBy(function ($val) {
           return Carbon::parse($val->created_at)->format('d/h');//d is for day// i change to h is define to hour
        }); 

        $getWatts = new wattsAndPower();       
        $getWatts->TotalWatts =$getTotalPowerFromGETDATA/1000;  // complete from 1 to until now  
        $getWatts->TotalVoltage = 220; 
        
        $collectionDateDays = new Collection();
      
        //Start Display for Line Type 
        $getFilterDataDaysAndHours = []; 
        foreach($collectionByDaysAndHours as $key => $daysItem)
        {  
                $getFilterDataDaysAndHours[]= $daysItem->sum('power'); 
        } 

        $getWatts->parseTotalVoltage = $getFilterDataDaysAndHours;
        $getWatts->daysDataAndHours = $collectionByDaysAndHours;
        $getWatts->collectionDate =Arr::pluck($collectionByDaysAndHours, '0.created_at');
        //End Display for Line Type


        
        //Start Display for Bar Type    
        $getFilterDataDaysOnly = [];
        foreach($collectionByDaysOnly as $key => $item)
        {
            $getFilterDataDaysOnly[] = $item->sum('power');
        }
        $getWatts->getDataforBarChart =$getFilterDataDaysOnly;
        // $getWatts->daysDataOnly = $collectionByDaysOnly;
        $getWatts->collectionDate_BarType = Arr::pluck($collectionByDaysOnly, '0.created_at'); 
        //End Display for Bar Type
 
        $getWatts->getRoomData = $collectionSample; 
        return response()->json($getWatts);  
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
         $id = $getData != null || $getData != ''? $getData: 0;
        
        if($id > 0)
            {  
            return Room::where('id','=',$id)
            ->orderBy('rooms.created_at','desc')
            ->get();
            }
        
        else
        
            return Room::where('id','>',0)   
            ->orderBy('rooms.created_at','desc')
            ->get();
    }
    
 
}
