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

function secToTime(sec){
	
	var secs = sec;

	if ( 1 == sec ) {
		return "1 <br>second old";
	}

	if ( sec < 60 ) {
		return sec + " <br>seconds old";
	}

	/* more than one minute */

	var out = "";

	var days= Math.floor(sec/(24*60*60));
	sec = sec - (Math.floor(sec/(24*60*60))*(24*60*60));
	
	var hours= Math.floor(sec/(60*60));
	sec = sec - (Math.floor(sec/(60*60))*(60*60));

	var minutes= Math.floor(sec/(60));
	sec = sec - (Math.floor(sec/(60))*(60));
	
	out = ('00'+hours).slice(-2)+":"+('00'+minutes).slice(-2)+":"+('00'+sec).slice(-2);

	if ( 1 == days ) {
		out = days + " day, "+('00'+hours).slice(-2)+":"+('00'+minutes).slice(-2)+":"+('00'+sec).slice(-2);
		//var d = new Date();
		//var n = d.getTime();
		//d = new Date( n - (secs*1000));
		//out+= +" <br> "+ d.getMonth();
		//console.log(d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds());
	}

	if ( days > 1 ) {
		out = days + " days, "+('00'+hours).slice(-2)+":"+('00'+minutes).slice(-2)+":"+('00'+sec).slice(-2) ;
		//var d = new Date();
		//var n = d.getTime();
		//d = new Date( n - (secs*1000));
		//out+= +" <br> "+ d.getMonth();
		//console.log(d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds());

	}

	out+=" (hours:minutes:seconds)";

	return out;
	
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
	} else {
		tempUnit = "<span class=\"small\" >F</span>";
	}

	/* Applys changes */
	$("#external_temp").html(crunchTemp(heldData.tempExtC_last)+"&deg;"+tempUnit);
	$("#min_external_temp").html(crunchTemp(heldData.minTempExtC)+"&deg;"+tempUnit+"<br><span class='smallTime'>"+heldData.minExtTemp_time+"</span>");
	$("#max_external_temp").html(crunchTemp(heldData.maxTempExtC)+"&deg;"+tempUnit+"<br><span class='smallTime'>"+heldData.maxExtTemp_time+"</span>");

	$("#internal_temp").html(crunchTemp(heldData.tempIntC_last)+"&deg;"+tempUnit);
	$("#min_internal_temp").html(crunchTemp(heldData.minTempIntC)+"&deg;"+tempUnit+"<br><span class='smallTime'>"+heldData.minIntTemp_time+"</span>");
	$("#max_internal_temp").html(crunchTemp(heldData.maxTempIntC)+"&deg;"+tempUnit+"<br><span class='smallTime'>"+heldData.maxIntTemp_time+"</span>");
}

var heldData;

function loadCabuData(){
	var url = "cabujson.php?station_id="+station_id;

	$.getJSON(url, 
	function(data) {
		/*
		gotta still make the null exceptions for these. There might be a nice way to handle
		 that with a method that excepts the value to be displayed and returns it the same if it isn't null
		*/
		seconds=data.ageSeconds;
		timeZone = data.timeZone;
		
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

		$("#external_temp").html(crunchTemp(data.tempExtC_last)+"&deg;"+tempUnit);
		$("#min_external_temp").html(crunchTemp(data.minTempExtC)+"&deg;"+tempUnit); //+tempUnit+"<br><span class='smallTime'>"+data.minExtTemp_time+"</span>");
		$("#max_external_temp").html(crunchTemp(data.maxTempExtC)+"&deg;"+tempUnit); //+tempUnit+"<br><span class='smallTime'>"+data.maxExtTemp_time+"</span>");

		$("#minExtTemp").attr("title","Minimum temperature occurred at:\n"+data.minExtTemp_time+" ("+timeZone+")");
		$("#maxExtTemp").attr("title","Maximum temperature occurred at:\n"+data.maxExtTemp_time+" ("+timeZone+")");

		$("#internal_temp").html(crunchTemp(data.tempIntC_last)+"&deg;"+tempUnit);
		$("#min_internal_temp").html(crunchTemp(data.minTempIntC)+"&deg;"+tempUnit); //+"<br><span class='smallTime'>"+data.minIntTemp_time+"</span>");
		$("#max_internal_temp").html(crunchTemp(data.maxTempIntC)+"&deg;"+tempUnit); //+"<br><span class='smallTime'>"+data.maxIntTemp_time+"</span>");

		$("#minIntTemp").attr("title","Minimum temperature occurred at:\n"+data.minIntTemp_time+" ("+timeZone+")");
		$("#maxIntTemp").attr("title","Maximum temperature occurred at:\n"+data.maxIntTemp_time+" ("+timeZone+")");


		$('#reportDate').html('<span class="emph">'+data.packet_date_last.substring(0,10)+''+data.packet_date_last.substring(10,19)+' </span>('+timeZone+')');

		if(null != data.minBatteryStateOfCharge_percent){
			$("#batt_charge_cabu").html("<span class='emph'>"+data.batteryStateOfCharge_percent_last+"</span><br>( "+data.vUPS_last+" volts)");
			$("#minBatt").html("<span class='emph'>"+data.minBatteryStateOfCharge_percent+
				"</span><br>( "+data.minBatteryStateOfCharge+" volts)"); //<br><span>"+data.minBatteryStateOfCharge_time+"</span>
			
			$("#minBatt").attr("title","Minimum charge occurred at:\n"+data.minBatteryStateOfCharge_time+" ("+timeZone+")");

			$("#maxBatt").html("<span class='emph'>"+data.maxBatteryStateOfCharge_percent+
				"</span><br>( "+data.maxBatteryStateOfCharge+" volts)");//<br><span>"+data.maxBatteryStateOfCharge_time+"</span>

			$("#maxBatt").attr("title","Maximum charge occurred at:\n"+data.maxBatteryStateOfCharge_time+" ("+timeZone+")");

		} else {
			$("#batt_charge_cabu").html('No data');
			$("#minBatt").html('No data for today');
			$("#maxBatt").html('No data for today');
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

	$('#age').html(secToTime(seconds));
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



$(document).ready(function(){


	
//	loadCabuData();
	timerTick();
	
	  
	
});
