//requires date.js and jquery and flot libraries
var speedChart=[];
var gustChart=[];

var date = new Date();
var cYear = date.getFullYear();
var cMonth = "0"+(date.getMonth()+1);
var cDay = date.getDate();

var minDate = cYear+"-"+cMonth+"-01";
var todayDate = cYear+"-"+cMonth+"-"+daysInMonth(cMonth,cYear);

var xMin = Math.round(new Date(minDate).getTime()/1000);
var xMax = Math.round(new Date(todayDate).getTime()/1000)+(24*60*60) ;
var yMax = 0;

var seconds=0;
var mod=2.23;
var dec = 1;



function commaSeparateNumber(val){
	if(null != val){
		while (/(\d+)(\d{3})/.test(val.toString())){
			val = val.toString().replace(/(\d+)(\d{3})/, '$1'+','+'$2');
		}
	}
	return val;
}


function loadSpeedChart(){

	
	
	$.plot("#flot", [{
		data: gustChart,
		bars: {
			show: true,
			barWidth: 60*60*12,
			fill: 1,
			align: "left"
		},
		color: 'red'
	},{
		data: speedChart,
		bars: {
			show: true,
			barWidth: 60*60*12,
			fill: 1,
			align: "left"
		
			
		},
		color: 'blue'
	}], {
		xaxis: {
			min: xMin,
			max: xMax,
			
			ticks: updateticksarray(),
			tickFormatter: function (val) {
				var xdate = new Date(val * 1000)
				return xdate.toString("dd")
			}
		},
		
		yaxis: {
			min: 0,
			max: yMax+(yMax*.1),
			ticks: (yMax/5),
			tickFormatter: function (val) {
				
				return (val*mod).toFixed(0);
			}
		}
	});
	$("#yearMonth").html(cYear+"-"+("0"+cMonth).slice(-2));
}

function nextMonth(){
	if(cMonth != 12){
		
		cMonth++;
		
	}else{
		cYear++;
		cMonth = 1;
	}
	minDate = cYear+"-"+("0"+cMonth).slice(-2)+"-01";
	todayDate = cYear+"-"+("0"+cMonth).slice(-2)+"-"+daysInMonth(cMonth,cYear);

	xMin = Math.round(new Date(minDate).getTime()/1000);
	xMax = Math.round(new Date(todayDate).getTime()/1000)+(24*60*60);
	loadSpeedChart();
}

function prevMonth(){
	if(cMonth != 1){
		
		cMonth--;
		
	}else{
		cYear--;
		cMonth = 12;
	}
	
	minDate = cYear+"-"+("0"+cMonth).slice(-2)+"-01";
	todayDate = cYear+"-"+("0"+cMonth).slice(-2)+"-"+daysInMonth(cMonth,cYear);
		
	xMin = Math.round(new Date(minDate).getTime()/1000);
	xMax = Math.round(new Date(todayDate).getTime()/1000)+(24*60*60);
	loadSpeedChart();
}

function daysInMonth(month,year) {
	return new Date(year, month, 0).getDate();
}
var fiddle = 12;
function updateticksarray() {
	ticksarray = []
	//var d = new Date();
	var tick = Math.floor((24 * 60 * 60)); //this is the amount of seconds inbetween each tick. this is a day's worth of seconds
	var start = xMin;
	//ticksarray.push(start+(24*60*60));
	for (var i = 0; start <= xMax; i++) {
		start += tick;
		ticksarray.push(start-(18*60*60));
	}

	return ticksarray;
}

function Fiddle(val){
	fiddle = val;
	loadSpeedChart();
}

function toggleUnit(){
	
	console.log(mod);
	if(mod==1){
		mod=2.23;
		$("#speedUnit").html("MPH");
	}else{
		mod=1;
		$("#speedUnit").html("m/s");
	}
	//loadData();
	loadSpeedChart();

}

