//requires date.js and jquery and flot libraries
var speedChart=[];
var gustChart=[];
var battChart=[];

var xMin = Math.round(new Date().getTime() / 1000)-86400 ;
var xMax = Math.round(new Date().getTime() / 1000) ;
var yMax = 0;


$(document).ready(function(){

	timerTickGraph();
	var button = document.getElementsByName ("button");
	
	button.value="show";

	var hover_color="#80B2CC";	
	
	$("#status").hide();

	$(".speedBlock").hover(function(){
		$(".speedBlock").css("background-color", hover_color);
	},function(){
		$(".speedBlock").css("background-color","#ffffff");
	});
	
	$(".battBlock").hover(function(){
		$(".battBlock").css("background-color", hover_color);
	},function(){
		$(".battBlock").css("background-color","#ffffff");
	});

	$(".speedBlock").click(function(){
		console.log("load speed chart");
		loadSpeedChart();
	});
	$(".battBlock").click(function(){
		console.log("load battery chart");
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

		$('#uptime').html(commaSeparateNumber(data.uptime_status));//add commas
		
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
		
	if(button.value=="show"){
		$("#status").show();
		button.value="hide";
		$("#button").html("Hide Status");
	}else{
		$("#status").hide();
		button.value="show";
		$("#button").html("Show Status");
	}
}

function toggleUnit(){
	
	console.log(mod);

	if(mod==1){
		mod=2.23;
	}else{
		mod=1;
	}

	loadData();

	loadSpeedChart();

}

