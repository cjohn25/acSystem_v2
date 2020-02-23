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
  
<form id="setupScheduleForm" name="setupScheduleForm" role="form" class="form-horizontal form-groups-bordered">
    {{-- @csrf --}}
    <div class="col-md-12" style="height: 100px">
        <div class="form-group">
            <div class="col-sm-3 input-group">

                <p>Date From:
                    <i class="entypo-calendar"></i>
                    <input type="text" id="datepicker" name="datepicker" class="form-control"></p>
                <select name="selectRoom" id="selectRoom" class="form-control">

                    <option value="">Select Room</option> 
                    {{-- @foreach ($getRoomData as $item)
                <option value="">{{$item}}</option>
                    @endforeach --}}
                </select>
            </div>

            <div class="col-sm-3 input-group">
                <p>Date To:
                    <i class="entypo-calendar"></i>
                    <input type="text" id="datepicker2" name="datepicker2" class="form-control"></p>

                <input type="submit" class="btn btn-primary" value="APPLY">
            </div>

            {{-- <p>Date: <input type="text" id="datepicker"></p> --}}
            {{-- <div class="form-group"> <label class="col-sm-3 control-label">Date Range w/ Predefined Ranges</label>
                <div class="col-sm-5">
                    <div class="daterange daterange-inline add-ranges active" data-format="MMMM D, YYYY"
                        data-start-date="February 8, 2020" data-end-date="March 3, 2020"> <i
                            class="entypo-calendar"></i> <span>February 8, 2020 - March 3, 2020</span> </div>
                </div>
            </div> --}}
            <div class="col-sm-6">

                <div class="outer1" style="height: 130px !important; ">
                    <div id="gauge_div" style="float:left;height:100%;"></div>
                    {{-- <div id="gauge_div2" style="float:left;height:100%"></div> --}}
                </div>
            </div>
        </div>
    </div>


    <div class="panel-body">
        <div id="checkData">
            <div class="loader"></div>
        </div>
        <div class="col-md-12">
            <div class="chart-container" id="myChartBarContainer" >
                <canvas id="myChartBar"></canvas>
            </div>
        </div>
        <div class="col-md-12" style="height:100px;"><br><br><br><br></div>
        <div class="col-md-12">
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
        $(function () {
            $("#datepicker").datepicker();
            $("#datepicker2").datepicker(); 
        });
        //Per hour Average
        var getDataforChart = [];


        // var ctxbar = document.getElementById('myChartBar');
        document.getElementById("myChartBarContainer").innerHTML = '&nbsp;';
        document.getElementById("myChartBarContainer").innerHTML = '<canvas id="myChartBar"></canvas>';
        var ctxbar = document.getElementById("myChartBar").getContext("2d");
        // myChartDisplayBar();
        function myChartDisplayBar(data) {
            confirmation = true; 
            // getData.push(data.parseTotalVoltage);  
            var myChart = new Chart(ctxbar, {
                type: 'bar',
                data: {
                    // labels: ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'],
                    labels: data.collectionDate_BarType,
                    datasets: [{
                        label: 'Watts',
                        // data:  [0,100],
                        data:data.getDataforBarChart != '' ? data.getDataforBarChart : 0, 
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
            // console.log(data1);
            // getData.push(data.parseTotalVoltage);  
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

        function drawGauge(response) {
            var num = 0;
            num = parseInt(response) != NaN ? parseInt(response) : 0;
            var data = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Total Watts', num]
            ]);
            var options = {
                min: 0,
                max: 9000,
                yellowFrom: 7000,
                yellowTo: 7999,
                redFrom: 8000,
                redTo: 9000,
                minorTicks: 5
            };
            var chart = new google.visualization.Gauge(document.getElementById('gauge_div'));
            chart.draw(data, options);
        }
 

        var getTotalVoltage = 0;

        var getTotalTotalWatts = 0; 
        var confirmation = false; 
        
        setInterval(function () {
            if(confirmation == true){ 
                callback(getTotalTotalWatts, getTotalVoltage);
            }
            else{ 
            }
            
        }, 5000);
       
        function callback(response, response1) {
            drawGauge(response);

            // drawGauge2(response1);
        }
        getChart();
        

        function getChart() {
            var selOpts="";
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
                        myChartDisplay(data);
                        myChartDisplayBar(data);
                        getTotalVoltage = data.TotalVoltage;
                        getTotalTotalWatts = data.TotalWatts;

                        for (var i = 0; i < data.getRoomData.length; i++) { 
                        selOpts += "<option value='" + data.getRoomData[i]['id'] + "'>" + data.getRoomData[i]['roomName'] + "</option>";
                        } 
                        $('#selectRoom').append(selOpts);
                        if(data != null){
                            $('#checkData').hide();
                        }
                        // callback(data.TotalVoltage, data.TotalWatts);
                    },
                    error: function (data) {
                        console.log('Error:', data);
                    }
                });
        }


        $('#setupScheduleForm').on('submit', function (e) {
            if($('#datepicker').val() != '' &&  $('#datepicker2').val() != '' && $('#selectRoom').val() != ''){
                e.preventDefault(); 
                    var form_data = $(this).serialize();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        data: form_data,
                        url: '/summary/dataPost',
                        type: "POST",
                        dataType: 'json',
                        success: function (data) {
                            console.log(data);
                            myChartDisplay(data); 
                            myChartDisplayBar(data);//display for Bar type 
                            getTotalVoltage = data.TotalVoltage; // for guage
                            getTotalTotalWatts = data.TotalWatts; // for guage
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
              
            }
            else {
  
                alert('Room and Date cannot be empty');
            }
   
        });
    });

</script>
@endsection
