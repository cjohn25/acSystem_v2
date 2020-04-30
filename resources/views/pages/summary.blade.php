@extends('layouts.index')

@section('content')

@if(session()->has('errorMessage'))
<div class="alert alert-danger">
    {{ session()->get('errorMessage') }}
</div>
@endif
<p style="text-align:left;font-weight:bolder;font-size:20px;background-color:#303641;color:#c8c8c8">
    <i class="entypo-gauge"></i> SUMMARY
</p>
{{-- <form action="{{route('showDashboard')}}" method="POST"> --}}

    <div class="col-sm-12">
        <button id="monthlyDate" class="btn btn-primary">This Month</button>
        <button id="weeklyDate" class="btn btn-primary">This Week</button>
        <button id="dailyDate" class="btn btn-primary">Today</button>
        <button id="hourlyDate" class="btn btn-primary">This Hour</button>
        <button id="Print" class="btn btn-primary">
            Print</button>
    </div>
<form id="setupScheduleForm" name="setupScheduleForm" role="form" class="form-horizontal form-groups-bordered">
    {{-- @csrf --}}
    <div class="col-md-12" >
        <br><br>
        <div class="form-group">
            <div class="col-sm-2 input-group">
                {{-- 
                <p>Date From:
                    <i class="entypo-calendar"></i>
                    <input type="text" id="datepicker" name="datepicker" class="form-control"></p> --}}
                <select name="selectRoom" id="selectRoom" class="form-control">
                    <option value=0>All Room</option>
                </select>
            </div>

            <div class="col-sm-4 input-group">
                <p>Date To:
                    <i class="entypo-calendar"></i>
                    <input type="text" id="datepicker2" name="datepicker2"
                        class="daterange daterange-inline add-ranges"></p>

                <input type="submit" class="btn btn-primary" value="APPLY">
            </div>

            {{-- <p>Date: <input type="text" id="datepicker"></p> --}}
            {{-- <div class="form-group"> <label class="col-sm-3 control-label">Date Range w/ Predefined Ranges</label>
                <div class="col-sm-5">
                    <div class="datera`nge daterange-inline add-ranges active" data-format="MMMM D, YYYY"
                        data-start-date="February 8, 2020" data-end-date="March 3, 2020"> <i
                            class="entypo-calendar"></i> <span>February 8, 2020 - March 3, 2020</span> </div>
                </div>
            </div> --}}
            <div class="form-group">
                    {{-- <div id="gauge_div" style="float:left;height:100%;"></div> --}}
                    <label class="col-sm-2  control-label">
                       <strong>Total Watts:</strong>
                    </label>
                    <div class="col-sm-3">
                    <input type="text" disabled id="showGauge" name="showGuage" class="form-control"
                        style="float:left;font-size:25px">
                    </div>
                    {{-- <div id="gauge_div2" style="float:left;height:100%"></div> --}}
            </div>
        </div>
    </div>

    <div class="panel-body">
        <div id="checkData">
            <div class="loader"></div>
        </div>
        <h2>
            <div id="summaryShow">TOTAL &nbsp;SUMMARY&nbsp; OF &nbsp;<span id="submittername"></span> </div>
        </h2>
        <div class="col-md-4" style="overflow:scroll;height:245px;overflow:auto"> 
        <table  id="list_table_json"> 
            <thead >
                <tr>
                    <th width="10%" style="font-size: 18px;" >Day</th>
                    <th width="15%"  style="font-size: 18px;" >Sum</th>
                </tr>
            </thead>
            <tbody>
                {{-- <tr> 
                </tr> --}}
            </tbody>
            <tfoot> 
                <tr>
                    <td style="font-size: 18px;" ><h4>Total:</h4></td>
                    <td  style="font-size: 18px;"><hr><span id="totalAmount"></span></td>
                </tr>
            </tfoot>
        </table>
    </div>
        <div class="col-md-8">
            <div class="chart-container" id="myChartBarContainer">
                <canvas id="myChartBar"></canvas>
            </div>
        </div>  
        <div class="col-md-8">
            <div class="chart-container" id="myChartContainer" style="position: relative;height:80vh; width:75vw">

                <canvas id="myChart"></canvas>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane" id="area-chart">
                <div id="area-chart-demo" class="morrischart" style="height: 300px"></div>
            </div>
        </div>

    </div>
</form>


<link rel="stylesheet" href="{{asset('assets/css/charts/Chart.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/charts/Chart.min.css')}}">
<script src="{{asset('assets/css/charts/Chart.bundle.js')}}"></script>
<script src="{{asset('assets/css/charts/Chart.bundle.min.js')}}"></script>
<script src="{{asset('assets/css/charts/Chart.js')}}"></script>
<script src="{{asset('assets/css/charts/Chart.min.js')}}"></script>
<script src="{{asset('assets/js/guage/loader.js')}}"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#checkData').show();
        $('#summaryShow').hide();
        $('#list_table_json').hide();
        $(function () {
            $("#datepicker").datepicker(); 
        });
        //Per hour Average
        var getDataforChart = []; 
        document.getElementById("myChartBarContainer").innerHTML = '&nbsp;';
        document.getElementById("myChartBarContainer").innerHTML = '<canvas id="myChartBar"></canvas>';
        var ctxbar = document.getElementById("myChartBar").getContext("2d"); 
        function myChartDisplayBar(data, getDateFiltered) {
            console.log(getDateFiltered);
            confirmation = true; 
            var myChart = new Chart(ctxbar, {
                type: 'bar',
                data: {
                    // labels: ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'],
                    labels: getDateFiltered,
                    datasets: [{
                        label: 'kWh',
                        // data:  [0,100],
                        data: data.getDataforBarChart != '' ? data.getDataforBarChart : 0,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 3
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        }



        document.getElementById("myChartContainer").innerHTML = '&nbsp;';
        document.getElementById("myChartContainer").innerHTML = '<canvas id="myChart"></canvas>';
        var ctx = document.getElementById("myChart").getContext("2d");

        function myChartDisplay(data1) {
            confirmation = true; 
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    // labels: ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'],
                    labels: data1.collectionDate,
                    datasets: [{
                        label: 'Watts',
                        data: data1.parseTotalVoltage != '' ? data1.parseTotalVoltage : 0,
                        // data:getDataforChart,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        }

        //Weekly Average

        // function showChart() {
        //     $.ajaxSetup({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         }
        //     });
        //     // var rID = $('#roomid').val();
        //     $.ajax({
        //         url: '/summary/data/',
        //         type: "POST",
        //         dataType: 'json',
        //         success: function (data) {
        //             console.log(data);
        //         },
        //         error: function (data) {
        //             console.log('Error:', data);
        //         }
        //     });
        // }


        google.charts.load('current', {
            'packages': ['gauge']
        });
        google.charts.setOnLoadCallback();
        // function drawGauge(response) {
        //     var num = 0;
        //     num = parseInt(response) != NaN ? parseInt(response) : 0;
        //     var data = google.visualization.arrayToDataTable([
        //         ['Label', 'Value'],
        //         ['Total Watts', num]
        //     ]);
        //     var options = {
        //         min: 0,
        //         max: 9000,
        //         yellowFrom: 7000,
        //         yellowTo: 7999,
        //         redFrom: 8000,
        //         redTo: 9000,
        //         minorTicks: 5
        //     };
        //     var chart = new google.visualization.Gauge(document.getElementById('gauge_div'));
        //     chart.draw(data, options);
        // }

        var getTotalVoltage = 0;

        var getTotalTotalWatts = 0;
        var confirmation = false;

        setInterval(function () {
            if (confirmation == true) {
                // callback(getTotalTotalWatts, getTotalVoltage);
                $('#showGauge').val(getTotalTotalWatts);
            } else {}
        }, 5000);

        // function callback(response, response1) {
        //     drawGauge(response);

        //     // drawGauge2(response1);
        // }
        getChart();


        function getChart() {
            var selOpts = "";
            var getDateFiltered = [];
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var getDataRoom = [];
            var getDataRoomSet = "";
            $.ajax({
                url: '/summary/data',
                type: "GET",
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    for (var x = 0; x < data.collectionDate_BarType.length; x++) {
                        getDateFiltered[x] = data.collectionDate_BarType[x].split(' ')[0];
                    }

                    myChartDisplay(data);
                    myChartDisplayBar(data, getDateFiltered);
                    getTotalTotalWatts = data.TotalWatts;
                    $('#showGauge').val(data.TotalWatts);
                    for (var i = 0; i < data.getRoomData.length; i++) {
                        selOpts += "<option value='" + data.getRoomData[i]['id'] + "'>" + data
                            .getRoomData[i]['roomName'] + "</option>";
                    }
                    $('#selectRoom').append(selOpts);
                    if (data != null) {
                        $('#checkData').hide();
                    }
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        }


        $('#setupScheduleForm').on('submit', function (e) {
            
                 $('#checkData').show();
                $('#summaryShow').hide();
                $('#list_table_json').hide();
            
            if ($('#datepicker2').val() != '' && $('#selectRoom').val() != '') {
                e.preventDefault();
                var getRoom = $('#selectRoom').val();
                var datepick = $('#datepicker2').val();
                console.log("room" + getRoom);
                var getDateFiltered1 = []; 
                var array = new Array();
                array = datepick.split('-');
                // var newDate = (array[0] + "." + array[1]);

                var event_data = '';
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    data: 'dateFrom=' + array[0] + '&dateTo=' + array[1] + '&room=' + getRoom,
                    url: '/summary/dataPost',
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        myChartDisplay(data);
                        myChartDisplayBar(data); //display for Bar type  
                        getTotalVoltage = data.TotalVoltage; // for guage
                        getTotalTotalWatts = data.TotalWatts; // for guage
                        
                    $('#checkData').hide();
                        for (var x = 0; x < data.collectionDate_BarType.length; x++) {
                            getDateFiltered1[x] = data.collectionDate_BarType[x].split(' ')[
                                0];
                                event_data+="<tr>"+
                                "<td>" +data.collectionDate_BarType[x].split(' ')[0]+"</td>"+
                               "<td>"+data.getDataforBarChart[x]+"</td>"+
                                "</tr>" 
                        }
                        
                        $("#totalAmount").html(data.barChartTotalSum);
                        myChartDisplayBar(data, getDateFiltered1);
                        $('#summaryShow').slideDown(); 
                        $('#list_table_json').slideDown();
                        $("#submittername").html($('#selectRoom').find('option:selected')
                            .text());
 
                        $("#list_table_json").append(event_data);
                    },
                    error: function (data) {
                        console.log('Error:', data);
                    }
                });

            } else {

                alert('Room and Date cannot be empty');
            }

        });

        $('#monthlyDate').on('click',function(){
            var get = getDate("month");
            console.log(get);
        });

        $('#weeklyDate').on('click',function(){
            
            var get =getDate("week");
            console.log(get);
        });

        $('#dailyDate').on('click',function(){ 
            var get =getDate("day");
            console.log(get);
        });
        $('#hourlyDate').on('click',function(){ 
            var get =getDate("hour");
            console.log(get);
        });
        
        $('#Print').on('click',function(){   
            pdfDownload(); 
        }); 
     
        function getDate(select)
        {
            var dateData = new Date();
            var date = new Date(Date.parse(dateData, new Date().getFullYear()));
            var year = new Date().getFullYear();
            var DateFull; 
            var getDate = moment().format('l'); 
            switch(select){
                case "month": 
                var month = new Date(Date.parse(dateData + 1, new Date().getFullYear())).getMonth()+1; 
                var firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDate();
                var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate(); 
                return DateFull = { 
                    DateFrom:  month+"/"+firstDay+"/"+year,
                    DateTo:  month+"/"+lastDay+"/"+year
                }  
                case "week": 
                var firstday = moment().startOf("week").format('l');
                var lastday = moment().endOf("week").format('l'); 
                return DateFull = { 
                    DateFrom:  firstday,
                    DateTo: lastday
                } 

                case "day":
                var firstday = moment().startOf("day").format('LT');
                var lastday = moment().endOf("day").format('LT');
                
                return DateFull = { 
                    DateFrom:  getDate+' '+firstday,
                    DateTo: getDate+' '+lastday
                }  
                case "hour":
                var startHour = moment().startOf("hour").format('LT');
                var endHour = moment().endOf("hour").format('LT');
                return DateFull = { 
                    DateFrom:  getDate+' '+ startHour,
                    DateTo:  getDate+' '+endHour
                } 
                default:
                break;
            }  
        }
    });
    function pdfDownload(){
            $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                console.log($('#list_table_json').val());
                $.ajax({  
                url: '/customer/print-pdf',
                type: "POST",
                dataType: 'json',
                success: function (data) {
                   console.log(data);
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        }
</script>
@endsection
{{-- 

<p>Click the button to display the name of this month.</p>

<button onclick="myFunction()">Try it</button>

<p id="demo"></p>

<script>
function myFunction() {
  var day = new Array();
  day[1] = "Monday";
  day[2] = "Tuesday";
  day[3] = "Wednesday";
  day[4] = "Thrusday";
  day[5] = "Friday";
  day[6] = "Saturday";
  day[7] = "Sunday"; 

  var d = new Date();
  var n = day[d.getDay()];
  document.getElementById("demo").innerHTML = n;
}
</script> --}}
