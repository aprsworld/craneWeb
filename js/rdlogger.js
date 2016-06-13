//requires date.js and jquery and flot libraries
var speedChart=[];
var gustChart=[];
var battChart=[];

var xMin = Math.round(new Date().getTime() / 1000)-86400 ;
var xMax = Math.round(new Date().getTime() / 1000) ;
var yMax = 0;
var statusObject = {
	showStatus : false,
	windGraph : true
	
}

$(document).ready(function(){

	timerTickGraph();
	var button = document.getElementsByName ("button");
	
	button.value="show";

	var hover_color="#0550a5";
	var hover_text_color="#fff"
	
	$("#status").hide();

	$(".speedBlock").hover(function(){
		$(".speedBlock").css("background-color", hover_color);
		$(".speedBlock").css("color", hover_text_color);

	},function(){
		$(".speedBlock").css("background-color","#ffffff");
		$(".speedBlock").css("color", "#333");

	});
	
	$(".battBlock").hover(function(){
		$(".battBlock").css("background-color", hover_color);
		$(".battBlock").css("color", hover_text_color);

	},function(){
		$(".battBlock").css("background-color","#ffffff");
		$(".battBlock").css("color", "#333");
	});

	$(".speedBlock").click(function(){
		console.log("load speed chart");
		$('#graphToggle').text("Toggle Battery Graph");
		statusObject.windGraph = true;
		loadSpeedChart();
	});
	$(".battBlock").click(function(){
		console.log("load battery chart");
		$('#graphToggle').text("Toggle Wind Graph");
		statusObject.windGraph = false;
		
		loadBattChart();
	});

	$("#settings").hover(function(){
		$("#settings").css("background-color", hover_color);
		$("#gear").attr("src","gearwhite.png");
		$("#gearTip").show();
	},function(){
		$("#settings").css("background-color","#ffffff");
		$("#gear").attr("src","gear.png");
		$("#gearTip").hide();
	});
	$( document ).ajaxError(function(event){
		console.log("no response from json");
		$("#connection_warn").show();
		$("#connection_warn").append(event);
		//$("#cover").hide();
		//clearInterval(loadTimerVar);
	});
	loadData();
	timerTick();
	 $("#button").on("click", showStatus); 
	 $("#button1").on("click", toggleUnit); 
	 $("#graphToggle").on("click",toggleGraph);
});

function hideWarn(){
	$("#connection_warn").hide();
}

function commaSeparateNumber(val){
	if(null != val){
		while (/(\d+)(\d{3})/.test(val.toString())){
			val = val.toString().replace(/(\d+)(\d{3})/, '$1'+','+'$2');
		}
	}
	return val;
}

var seconds=0;
var mod=2.23;
var dec = 1;
function loadData(){
	var url = "json.php?station_id="+station_id;

	$.getJSON(url, 
	function(data) {
		hideWarn();
		tzOffset=data.timeZoneOffsetHours;
	
		$('#arrow').rotate({animateTo:(data.windDirectionSector_last*45)});		
		
		seconds=data.ageSeconds;

		$('#reportDate').html('Report Date:<br />'+data.packet_date_local.substring(0,10)+'<br /><span class="emph">'+data.packet_date_local.substring(10,19)+' </span><br>'+data.timeZone);

		$("#windSpeed").html(parseFloat(data.windSpeed_last*mod).toFixed(dec));
		$("#windGust").html(parseFloat(data.windGust_last*mod).toFixed(dec));
		
		//find percent of memory that is full
		var memPercent = (parseFloat(data.dataflashPage_status)/4095)*100;
		
		//4095 total pages of capacity minus current pages
		//44 lines of data per page of data flash and one line of data every minute so that is 44 minutes per page
		//there are 1440 minutes in a day
		var daysRemaining =  (parseFloat((4095-data.dataflashPage_status)*44))/1440;
		
		//round our numbers
		memPercent = Math.round(memPercent);
		daysRemaining = Math.round(daysRemaining); 
		
		if(memPercent >= 100.00){
			$("#memPercent").html("Memory Full");
			$("#memPercent").css("color", "red");
			$("#memDaysRemain").html("0");
			$("#memDaysRemain").css("color", "red");

		} else {
			$("#memPercent").html(memPercent+"%");
			$("#memPercent").css("color", "black");
			$("#memDaysRemain").html(daysRemaining);
			$("#memDaysRemain").css("color", "black");
		}
		var unit="m/s";

		if(mod==2.23){
			unit="MPH";			
		}

		$(".unit").html(unit);
		
		$("#maxGust").html('<span class="emph">Max Gust</span><br /><span>Last Hour:<span class="emph"> '+(data.maxWindGust_hour*mod).toFixed(dec)+'</span> '+unit+'</span><br /><span>Today:<span class="emph"> '+(data.maxWindGust_today*mod).toFixed(dec)+'</span> '+unit+'</span>');

				
		if(null != data.minBatteryStateOfCharge_today){
			$("#minBatt").html(data.minBatteryStateOfCharge_today+'%');
			$("#maxBatt").html(data.maxBatteryStateOfCharge_today+'%');
			$("#batt_charge").html(data.batteryStateOfCharge_last+'%');
		} else {
			$("#minBatt").html('No data for today');
			$("#maxBatt").html('No data for today');
			$("#batt_charge").html('?%');
		}


		//This doesn't work yet		
		if(data.batteryStateOfCharge_last>75){
			$("#battImg").attr("src","battery.png").load(function() {pic_real_height=100});	
		}else if(data.batteryStateOfCharge_last>60){
			$("#battImg").attr("src","batteryMid.png").load(function() {pic_real_height=100});
		}else{
			$("#battImg").attr("src","batteryLow.png").load(function() {pic_real_height=100});
		}

		$('#statusDate').html(data.packet_date_status + "<br>Received "+data.ageTime_status+" ago.");
		
		if ( 0 == data.sdStatus_status ) {
			$('#cardStatus').html("Logging");
			$("#cardStatus").css("background-color","#66FF66");
		} else {
			$('#cardStatus').html("Card not inserted!");
		}

		$('#uptime').html(commaSeparateNumber(data.uptime_status)+" Minutes");//add commas
		
		if ( 120 < seconds ) {
			$(".block").css("background-color","RED");
			$(".block").attr("title","Data is old");
		} else {
			$(".block").css("background-color","WHITE");
			$(".block").attr("title","");
		}
	
	});

}

function timerTick(){

	da= new Date();

	$('#age').html(secToTime(seconds)+" old");
	seconds++;
	
	if(seconds%10==1){
		//seconds = 0;
		loadData();	
	}

	if(seconds>60){
		$(".caution").show();
	}else{
		$(".caution").hide();
	}
	
	setTimeout(timerTick,1000);
}

function timerTickGraph(){

	
	loadHistory();
	setTimeout(timerTickGraph,1000*60*5);//check once every 5 minutes
}

function loadSpeedChart(){

	
	
	$.plot("#flot", [{
		data: gustChart,
		bars: {
			show: true,
			barWidth: 90,
			fill: 1,
			align: "center"
		},
		color: 'red'
	},{
		data: speedChart,
		bars: {
			show: true,
			barWidth: 90,
			fill: 1,
			align: "center"
			
		},
		color: 'blue'
	}], {
		
		xaxis: {
			min: xMin,
			max: xMax,
			ticks: updateticksarray(),
			tickFormatter: function (val) {
				//var xdate = new Date(val * 1000)
				//return xdate.toString("hh:mmtt<br />M/d")
				return applyTZ(val,tzOffset);
			}
		},
		
		yaxis: {
			min: 0,
			max: yMax,
			tickFormatter: function (val) {
				
				return (val*mod).toFixed(0);
			}
		}
	});
	
}

function loadBattChart(){
	$.plot("#flot", [{
		data: battChart,
		bars: {
			show: true,
			barWidth: 90,
			fill: 1,
			align: "center"
			
		},
		color: 'green'
	}], {
		xaxis: {
			min: xMin,
			max: xMax,
			ticks: updateticksarray(),
			tickFormatter: function (val) {
				//var xdate = new Date((val * 1000));
				//return xdate.toString("hh:mmtt<br />M/d");
				return applyTZ(val,tzOffset);
			}
		},
		yaxis: {
			min: 0,
			max: 100
		}
	});
}


var tzOffset;


function loadHistory() {
	var urlData = "JSONbacklog.php?station_id="+station_id+"&web=1";
	$.getJSON(urlData,
		function (dataHist) {
			tzOffset=dataHist.timeZoneOffsetHours;
			xMin = Math.round(new Date().getTime() / 1000)-86400 ;
			xMax = Math.round(new Date().getTime() / 1000) ;			
			//xMin = dataHist.block[0].packet_date;
			for(var i = 0; i< dataHist.block.length; i++){
				
				gustChart[i]=[dataHist.block[i].packet_date,dataHist.block[i].gust];
				speedChart[i]=[dataHist.block[i].packet_date,dataHist.block[i].speed];
				battChart[i]=[dataHist.block[i].packet_date,dataHist.block[i].battery];
				
				if(yMax<dataHist.block[i].gust){
					yMax=(dataHist.block[i].gust)*1.2;
				}
				
				
			}
			//xMax = dataHist.block[i-1].packet_date;
			
			loadSpeedChart();
			$(document).scrollTop( $("#wrapper").offset().top );
		});
	
}
function updateticksarray() {
	ticksarray = []
	var d = new Date();
	var tick = Math.floor((24 * 60 * 60) / 10); //converts hours to seconds then divides it by 10
	var start = Math.floor((d.getTime() / 1000) - (24 * 60 * 60));
	ticksarray.push(start);
	for (var i = 0; i < 10; i++) {
		start += tick;
		ticksarray.push(start);
	}
	ticksarray.push(Math.floor(d.getTime()/1000));
	return ticksarray;
}

function showStatus(){
	var button = document.getElementsByName ("button");
		console.log("fired "+statusObject.showStatus );
	if(statusObject.showStatus == false){
		$("#status").show();
		statusObject.showStatus = true;
		//button.value="hide";
		$("#button").html("Hide Status");
	}else if(statusObject.showStatus == true){
		$("#status").hide();
		statusObject.showStatus = false;

		//button.value="show";
		$("#button").html("Show Status");
	}
	else{
		console.log("failed "+statusObject.showStatus);	
	}
}
function toggleGraph() {
	console.log(statusObject.windGraph);
	if(statusObject.windGraph == false){
		console.log("load speed chart");
				statusObject.windGraph  = true;

		loadSpeedChart();
		$('#graphToggle').text("Toggle Battery Graph");
	}else if(statusObject.windGraph  == true){
		console.log("load battery chart");
				statusObject.windGraph  = false;

		loadBattChart();
		$('#graphToggle').text("Toggle Wind Graph");

	}
	else{
		console.log("failed "+statusObject.windGraph);	
	}
	
}
function toggleUnit(){
	
	console.log(mod);

	if(mod==1){
		$('#button1').text('Change to m/s');
		mod=2.23;
	}else{
		$('#button1').text('Change to MPH');

		mod=1;
	}

	loadData();
	statusObject.windGraph  = true;
			$('#graphToggle').text("Toggle Battery Graph");

	loadSpeedChart();

}

