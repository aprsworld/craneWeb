function secToTime(sec){
	
	var secs = sec;

	if ( 1 == sec ) {
		return "1 second ";
	}

	if ( sec < 60 ) {
		return sec + " seconds ";
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
		
	}

	if ( days > 1 ) {
		out = days + " days, "+('00'+hours).slice(-2)+":"+('00'+minutes).slice(-2)+":"+('00'+sec).slice(-2) ;
		

	}

	out+=" (hours:minutes:seconds)";

	return out;
	
}


function secToTimeDate(sec){
	
	var secs = sec;

	if ( 1 == sec ) {
		return "1 second ";
	}

	if ( sec < 60 ) {
		return sec + " seconds ";
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
		var d = new Date();
		var n = d.getTime();
		d = new Date( n - (secs*1000));
		//out+= +" <br> "+ d.getMonth();
		out+= " ("+d.getFullYear()+"-"+('00'+(d.getMonth()+1)).slice(-2)+"-"+('00'+d.getDate()).slice(-2)+" "+
			('00'+d.getHours()).slice(-2)+":"+('00'+d.getMinutes()).slice(-2)+":"+('00'+d.getSeconds()).slice(-2)+")";
		//console.log(d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds());
	}

	if ( days > 1 ) {
		out = days + " days, "+('00'+hours).slice(-2)+":"+('00'+minutes).slice(-2)+":"+('00'+sec).slice(-2) ;
		var d = new Date();
		var n = d.getTime();
		d = new Date( n - (secs*1000));
		//out+= +" <br> "+ d.getMonth();
		out+= " ("+d.getFullYear()+"-"+('00'+(d.getMonth()+1)).slice(-2)+"-"+('00'+d.getDate()).slice(-2)+" "+
			('00'+d.getHours()).slice(-2)+":"+('00'+d.getMinutes()).slice(-2)+":"+('00'+d.getSeconds()).slice(-2)+")";
		//console.log(d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds());

	}

	out+=" (hours:minutes:seconds)";

	return out;
	
}


function applyTZ(val, tzOffset){

	//get local date based off of browser
	d = new Date((val*1000));
	
	//convert the local date to utc by getting the timezone offset and applying it to the local date
	utc = d.getTime() + (d.getTimezoneOffset() * 60000);

	//apply the real timezone offset gotten from json
	nd = new Date(utc + (3600000*tzOffset));
	
	//return string in proper format
	return nd.toString("hh:mmtt<br />M/d");
}




