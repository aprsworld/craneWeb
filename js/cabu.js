/* requires ian.aprsworld.com/javascript/cookies.js */

var seconds=0;
var timeZone;

function commaSeparateNumber(val){
	if(null != val){
		while (/(\d+)(\d{3})/.test(val.toString())){
			val = val.toString().replace(/(\d+)(\d{3})/, '$1'+','+'$2');
		}
	}
	return val;
}



var tempUnit = "<span class=\"small\" >C</span>";

/* should be pretty obvious what this does. Converts degrees C to F */
function convertCtoF(degree) {
	return (degree * 9 / 5 + 32).toFixed(1);
}

/* Checks if temperature needs to be converted */
function crunchTemp( C ){
	if ( "<span class=\"small\" >F</span>" == tempUnit ) {
		return convertCtoF(C);
	} else {
		return C;
	}

}

/* toggles between C and F */
function toggleUnit() {
	
	if ( "<span class=\"small\" >F</span>" == tempUnit ) {
		tempUnit = "<span class=\"small\" >C</span>";
		$("#button1").html("Switch to &deg;F");
		setCookie("degree","C",365);
	} else {
		tempUnit = "<span class=\"small\" >F</span>";
		$("#button1").html("Switch to &deg;C");
		setCookie("degree","F",365);
	}

	/* Applys changes */
	if ( null != heldData ){
		if(crunchTemp(heldData.tempExtC_last) > -40 && crunchTemp(heldData.tempExtC_last) < 150){
			$("#external_temp").html(crunchTemp(heldData.tempExtC_last)+"&deg;"+tempUnit);
		}
		else{
			$("#external_temp").html("Not Connected");
		}
		if(crunchTemp(heldData.minTempExtC) > -40 && crunchTemp(heldData.minTempExtC) < 150){
			$("#min_external_temp").html(crunchTemp(heldData.minTempExtC)+"&deg;"+tempUnit);			
		}
		else{
			$("#min_external_temp").html("Not Connected");			
		}
		if(crunchTemp(heldData.maxTempExtC) > -40 && crunchTemp(heldData.maxTempExtC) < 150){
			$("#max_external_temp").html(crunchTemp(heldData.maxTempExtC)+"&deg;"+tempUnit);
		}
		else{
			$("#max_external_temp").html("Not Connected");			
		}
		$("#internal_temp").html(crunchTemp(heldData.tempIntC_last)+"&deg;"+tempUnit);
		$("#min_internal_temp").html(crunchTemp(heldData.minTempIntC)+"&deg;"+tempUnit);
		$("#max_internal_temp").html(crunchTemp(heldData.maxTempIntC)+"&deg;"+tempUnit);
	}
}

var heldData;

function loadCabuData(){
	console.log(station_id);
	var url = "cabujson.php?station_id="+station_id;

	$.getJSON(url, 
	function(data) {
		/*
		gotta still make the null exceptions for these. There might be a nice way to handle
		 that with a method that excepts the value to be displayed and returns it the same if it isn't null
		*/
		console.log(data.TEST1);
		seconds=data.ageSeconds;
		timeZone = data.parentTimeZone;
		
		$("#charger_current").html(data.iCharger_last);
		$("#min_charger_current").html(data.minICharger); //+"<br><span class='smallTime'>"+data.minICharger_time+"</span>");
		$("#max_charger_current").html(data.maxICharger); //+"<br><span class='smallTime'>"+data.maxICharger_time+"</span>");

		$("#minCharger").attr("title","Minimum amps occurred at:\n"+data.minICharger_time+" ("+timeZone+")");
		$("#maxCharger").attr("title","Maximum amps occurred at:\n"+data.maxICharger_time+" ("+timeZone+")");

		$("#load_current").html(data.iLoad_last);
		$("#min_load_current").html(data.minILoad); //+"<br><span class='smallTime'>"+data.minILoad_time+"</span>"
		$("#max_load_current").html(data.maxILoad); //+"<br><span class='smallTime'>"+data.maxILoad_time+"</span>"

		$("#minLoad").attr("title","Minimum amps occurred at:\n"+data.minILoad_time+" ("+timeZone+")");
		$("#maxLoad").attr("title","Maximum amps occurred at:\n"+data.maxILoad_time+" ("+timeZone+")");
		if(crunchTemp(data.tempExtC_last) > -40 && crunchTemp(data.tempExtC_last) < 150){
			$("#external_temp").html(crunchTemp(data.tempExtC_last)+"&deg;"+tempUnit);
		}
		else{
			$("#external_temp").html("Not Connected");

		}
		if(crunchTemp(data.minTempExtC) > -40 && crunchTemp(data.minTempExtC) < 150){
			$("#min_external_temp").html(crunchTemp(data.minTempExtC)+"&deg;"+tempUnit); //+tempUnit+"<br><span class='smallTime'>"+data.minExtTemp_time+"</span>");
		}
		else{
			$("#min_external_temp").html("Not Connected");
		}
		if(crunchTemp(data.maxTempExtC) > -40 && crunchTemp(data.maxTempExtC) < 150){
			$("#max_external_temp").html(crunchTemp(data.maxTempExtC)+"&deg;"+tempUnit); //+tempUnit+"<br><span class='smallTime'>"+data.maxExtTemp_time+"</span>");
		}
		else{
			$("#max_external_temp").html("Not Connected");
		}
		$("#minExtTemp").attr("title","Minimum temperature occurred at:\n"+data.minExtTemp_time+" ("+timeZone+")");
		$("#maxExtTemp").attr("title","Maximum temperature occurred at:\n"+data.maxExtTemp_time+" ("+timeZone+")");

		$("#internal_temp").html(crunchTemp(data.tempIntC_last)+"&deg;"+tempUnit);
		$("#min_internal_temp").html(crunchTemp(data.minTempIntC)+"&deg;"+tempUnit); //+"<br><span class='smallTime'>"+data.minIntTemp_time+"</span>");
		$("#max_internal_temp").html(crunchTemp(data.maxTempIntC)+"&deg;"+tempUnit); //+"<br><span class='smallTime'>"+data.maxIntTemp_time+"</span>");

		$("#minIntTemp").attr("title","Minimum temperature occurred at:\n"+data.minIntTemp_time+" ("+timeZone+")");
		$("#maxIntTemp").attr("title","Maximum temperature occurred at:\n"+data.maxIntTemp_time+" ("+timeZone+")");


		$('#reportDate').html('<span class="emph">'+data.packet_date_local_last.substring(0,10)+''+data.packet_date_local_last.substring(10,19)+' </span>('+timeZone+')');

		if(null != data.minBatt){
			$("#and_batt").html("<span class='emph'>"+data.batt+"%</span>");
			$("#minBatt").html("<span class='emph'>"+data.minBatt+
				"%</span>"); 
			
			$("#minBatt").attr("title","Minimum charge occurred at:\n"+data.minBatt_time+" ("+timeZone+")");

			$("#maxBatt").html("<span class='emph'>"+data.maxBatt+
				"%</span>");

			$("#maxBatt").attr("title","Maximum charge occurred at:\n"+data.maxBatt_time+" ("+timeZone+")");

		} else {
			$("#and_batt").html('No data');
			$("#minBatt").html('No data for today');
			$("#maxBatt").html('No data for today');
		}

		if(null != data.minBatteryStateOfCharge_percent){
			$("#batt_charge_cabu").html("<span class='emph'>"+data.batteryStateOfCharge_percent_last+"</span><br>( "+data.vUPS_last+" volts)");
			$("#minCabBatt").html("<span class='emph'>"+data.minBatteryStateOfCharge_percent+
				"</span><br>( "+data.minBatteryStateOfCharge+" volts)"); //<br><span>"+data.minBatteryStateOfCharge_time+"</span>
			
			$("#minCabBatt").attr("title","Minimum charge occurred at:\n"+data.minBatteryStateOfCharge_time+" ("+timeZone+")");

			$("#maxCabBatt").html("<span class='emph'>"+data.maxBatteryStateOfCharge_percent+
				"</span><br>( "+data.maxBatteryStateOfCharge+" volts)");//<br><span>"+data.maxBatteryStateOfCharge_time+"</span>

			$("#maxCabBatt").attr("title","Maximum charge occurred at:\n"+data.maxBatteryStateOfCharge_time+" ("+timeZone+")");

		} else {
			$("#batt_charge_cabu").html('No data');
			$("#minCabBatt").html('No data for today');
			$("#maxCabBatt").html('No data for today');
		}


		if(null != data.minVehBatteryStateOfCharge_percent){
			$("#batt_veh_charge_cabu").html("<span class='emph'>"+data.batteryVehStateOfCharge_percent_last+"</span><br>( "+data.vVehicle_last+" volts)");
			$("#minVehBatt").html("<span class='emph'>"+data.minVehBatteryStateOfCharge_percent+
				"</span><br>( "+data.minVehBatteryStateOfCharge+" volts)"); //<br><span >"+data.minVehBatteryStateOfCharge_time+"</span>

			$("#minVehBatt").attr("title","Minimum charge occurred at:\n"+data.minVehBatteryStateOfCharge_time+" ("+timeZone+")");

			$("#maxVehBatt").html("<span class='emph'>"+data.maxVehBatteryStateOfCharge_percent+
				"</span><br>( "+data.maxVehBatteryStateOfCharge+" volts)"); //<br><span >"+data.maxVehBatteryStateOfCharge_time+"</span>

			$("#maxVehBatt").attr("title","Maximum charge occurred at:\n"+data.maxVehBatteryStateOfCharge_time+" ("+timeZone+")");

		} else {
			$("#batt_veh_charge_cabu").html('No data');
			$("#minVehBatt").html('No data for today');
			$("#maxVehBatt").html('No data for today');
		}

		heldData = data;

	});

}

function timerTick(){

	da= new Date();

	$('#age').html(secToTime(seconds)+" old");
	seconds++;
	
	if(seconds%10==1){
		//seconds = 0;
		loadCabuData();	
	}

	if(seconds>60){
		$(".caution").show();
	}else{
		$(".caution").hide();
	}
	
	setTimeout(timerTick,1000);
}

function checkTemp(){
	var deg = getCookie("degree");
	if (deg != "C") {
		toggleUnit();
	}

}

$(document).ready(function(){

//	loadCabuData();
	timerTick();
	checkTemp();
	
});
