function init_astrocalc(date,long,lat,az,roof,temp_coeff,timezone) {					
	tmp_debug="";
	skydome_coeff = 1; //skydome
	pi = 3.1415926536;
	RAD = pi/180.0; // degrees-radians
	// hight of sun at sunrise: radius+refraction
	h_sunrise   = -(50.0/60.0)*RAD;
	meteo_data = [];
	//sunhours, av min temp, av max temp, windspeed, source: http://www.meteo.be/meteo/view/nl/360955-Maandelijkse+normalen.html#ppt_5238199  
	meteo_data["Gemiddelde"]=[[59,77,114,159,191,188,201,190,143,113,66,45],[0.7,0.7,3.1,5.3,9.2,11.9,14.0,13.6,10.9,7.8,4.1,1.6],
						[5.7,6.6,10.4,14.2,18.1,20.6,23,22.6,19,14.7,9.5,6.1],[4.1,3.8,3.8,3.4,3.2,3,2.9,2.8,3,3.4,3.6,3.7]];
	meteo_data["2010"]=[[48.65,28.87,117.97,223.02,171.80,258.62,252.02,136.48,142.67,118.58,23.77,33.12],[0,0,0,0,0,0,0,0,0,0,0,0],
						[2.2,5,10.4,15.7,15.4,22.4,25.8,21.5,18.6,14.7,8.2,1.2],[2.9,4.1,4.1,3.5,3.2,2.8,2.9,3,2.7,3.6,3.5,2.8]];
	meteo_data["2011"]=[[52.28,54.75,204.22,238.85,264.20,180.98,140.00,144.68,173.38,161.75,114.9,51.93],[0,0,0,0,0,0,0,0,0,0,0,3.6],
						[6.7,8.1,12.4,19.6,20,21.7,20.1,21.9,21.2,16.5,12.5,8.7],[3.6,3.9,3.3,2.9,3.4,3.3,3.1,3,3.3,3.4,3.2,4.7]];
	meteo_data["2012"]=[[48.95,95.42,165.93,113.18,189.87,147.37,173.38,218.5,175.37,119.97,96.66,30.8],[2.7,-3.2,4.6,4.4,9.8,11.4,13.4,14.3,9.9,14.9,4.7,7.2],
						[8,3.6,13.3,12.6,19.1,19.5,21.8,24.3,19.5,7.5,9.6,2.5],[3.9,3.4,2.9,4,3.1,3.3,3,3.2,3,3.4,3.8,4.6]];
	meteo_data["2013"]=[[36.35,59.33,82.72,0,0,0,0,0,0,0,0,0],[0.0001,-0.9,-0.3,0,0,0,0,0,0,0,0,0],
						[4.1,3.7,6.1,0,0,0,0,0,0,0,0,0],[3.6,3.7,4.04,0,0,0,0,0,0,0,0,0]];
	longitude = long; //longitude of pv location
	latitude = lat; //latitude of pv location
	longRAD = long*RAD; // latitude in radians
	latRAD = lat*RAD; // latitude in radians
	pv_az = az*RAD; //azimuth in radians
	pv_roof = roof*RAD; //roof in radians
	pv_temp_coeff = temp_coeff //temperature coefficient pv module Pmax [%/K] with STC=25°C
	pv_eff = 1; //=1 taking into account that pv-modules have positive tolerance so that other losses can be neglected
	time_zone = timezone; //timetzone (normally 1 in CEST + 1 will be added by function for summertime)
	
	doy = dayofyear(date);
	TE = timeequation(doy);
 	DC = sun_declination(doy);
	G0 = global_radiation(doy);
	SR = sunrise(doy);
	SS = sunset(doy);
	city = fLTFarr(longRAD,latRAD);
	LTF = conv_avrg(city[3],doy);
	if (!document.getElementById('dataset')) dataset='Gemiddelde'; else {
		var selectElement = document.getElementById("dataset"); 
		dataset = selectElement.options[selectElement.selectedIndex].text; 
	}
	for(i=0;i<4;i++) {
		for(i1=0;i1<12;i1++) {
			if (meteo_data[dataset][i][i1]==0) meteo_data[dataset][i][i1]=meteo_data["Gemiddelde"][i][i1];
		}
	}
	location_min_temp_month = conv_avrg(meteo_data[dataset][1],doy);
	location_max_temp_month = conv_avrg(meteo_data[dataset][2],doy);
	location_windspeed = conv_avrg(meteo_data[dataset][3],doy);
}

function fLTFarr(lon1,lat1){
//lat,lon,ltf, wunderground
 var cities =[
			  	["Ukkel",50.801,4.333,[3.3,3.5,4.1,4.3,4.4,4.2,4.4,4.5,4.1,3.4,3.4,3.0],"EBBR"],
	 			["Aartselaar",51.134,4.385,[3.2, 3.4 ,4.0 ,4.0 ,4.3 ,4.1 ,4.3 ,4.4 ,4.0 ,3.4 ,3.3 ,3.0],"EBAW"],
	 			["Emblem",51.162,4.605,[3.3,3.4,4.0,4.1,4.3,4.1,4.3,4.5,4.1,3.4,3.4,3.0],"EBAW"],
	 			["Veurne",51.073,2.670,[3,3.1,3.8,3.4,3.9,3.9,4,4.1,3.7,3,3,2.8],"EBNF"],
	 			["Oostende",51.217,2.900,[3,3,3.8,3.4,3.9,3.9,4,4.2,3.7,3,3,2.8],"EBOS"],
	 			["Kortrijk",50.823,3.259,[3,3,3.9,3.3,3.8,3.9,3.8,4.1,3.5,3,3,2.9],"LFQQ"],
	 			["Gent",51.057,3.720,[3.1,3.1,3.9,3.7,4.1,4,4.1,4.3,3.8,3.2,3.2,2.9],"LFQQ"],
	 			["Turnhout",51.322,4.938,[3.2,3.2,3.9,3.9,4.2,4.1,4.3,4.5,4.1,3.5,3.2,3.1],"EHEH"],
	 			["Mechelen",51.025,4.477,[3.3,3.5,4.1,4.1,4.4,4.1,4.4,4.5,4.1,3.4,3.4,3],"EBBR"],
	 			["Leuven",50.878,4.704,[3.3,3.5,4.2,4.3,4.4,4.2,4.4,4.6,4.1,3.5,3.5,3],"EBBE"],
	 			["Hasselt",50.931,5.332,[3.3,3.4,4,4.1,4.4,4.2,4.5,4.5,4.1,3.4,3.4,3],"EBBL"],
	 			["Tienen",50.808,4.943,[3.3,3.5,4.1,4.2,4.4,4.2,4.4,4.5,4.1,3.5,3.4,3],"EBBE"],
	 			["Groningen",53.2,6.6,[2.6,2.3,3.1,2.9,3.2,3.4,3.9,4.1,3.9,3.5,2.6,2.8,3.2],"EHGG"],	 
	 			["Amsterdam",52.370,4.895,[2.7,2.5,3.5,3.2,3.4,3.8,4,4.5,4,3.6,2.5,2.7],"EHAM"], 
	 			["Arnhem",52.068,5.896,[2.7,2.4,3.5,3.2,3.5,4,4.3,4.7,4.1,3.6,2.5,2.8],"EHDL"]
			];	
 //calculate distance, source: http://www.movable-type.co.uk/scripts/latlong.html	 
 var R = 6371; // km
 var min_d_city=0;
 var min_d=99999;
 for(var i=0;i<cities.length;i++) {
  var lat2=	cities[i][1]*RAD;
  var lon2=	cities[i][2]*RAD;
  var d = Math.acos(Math.sin(lat1)*Math.sin(lat2)+Math.cos(lat1)*Math.cos(lat2)*Math.cos(lon2-lon1)) * R;
  if (d<min_d) {min_d=d;min_d_city=i;}
 }
 return cities[min_d_city];
}

function conv_avrg(in_array,doy) {
	var m=0;
	var d=0;
	var new_in_array=[];
	while (d<doy-15) {
		d=d+daysInMonth(m+1,2011);
		m++;
	} 
	in_array.unshift(in_array[11]);
	in_array.push(in_array[1]);
	for(var i=1;i<=12;i++) {
		new_in_array[i]=1.5*in_array[i]-(in_array[i-1]+in_array[i+1])/4;
	}
	new_in_array[0]=new_in_array[12];
	new_in_array[13]=new_in_array[1];
	var basem=new_in_array[m]; var nextm=new_in_array[m+1];
	var ddiff=doy-d+15;
	return basem+(nextm-basem)/30*ddiff;
}

//simulated SMA SB5000-TL20
function generator_coeff(tot_en){
//	var a=0.16+Math.pow((tot_en-1/100),1/8)-0.2*tot_en;
	var a=0.07+Math.pow(tot_en,1/14)-0.1*tot_en;
	if (a>0) return a; else return 0;
}

//simulated panel
function panel_coeff(tot_en) {
//    var a=0.25+Math.pow(tot_en,1/4)-0.25*tot_en;
    var a=-1.4+2.5*Math.pow(tot_en,1/16)-0.1*tot_en;
	if (a>0) return a; else return 0;
}

function angular_reflection(angle) {
	//source: http://onlinelibrary.wiley.com/doi/10.1002/pip.585/pdf
	var ar=0.16;
	return (1-Math.exp(-1*angle/ar))/(1-Math.exp(-1/ar));
}

//source: adapted from http://cse.fraunhofer.org/Default.aspx?app=LeadgenDownload&shortpath=docs%2fEUPVSEC_2010_Light_Transmission.pdf
function windspeed() {
 var a= 1.05-0.05*location_windspeed;
 if (a>0) return a; else return 0;
}

//source: own formula to simulate temperature as f(time)
function real_temp(curr_time) {
 var a=	location_min_temp_month+0.5*(location_max_temp_month-location_min_temp_month)*(1+Math.sin( pi*(curr_time-SR)/(SS-SR)-0.5 ) );
 return a;
}

//source http://pvcdrom.pveducation.org/MODULE/NOCT.htm
function panel_temp (tot_en, doy, curr_time) {
	var noct=47; //average, depending on module
	var a= real_temp(curr_time)+(noct-20)/80*100*tot_en*windspeed();
	return a;
}

function temperature_coeff (temp) {
	var a= 1+(temp-25)*pv_temp_coeff/100;
	return a;
//	if (a>0) return a; else return 0;
}
function dayofyear(indate) {   // indate YYYYMMDD
    var d = new Date(parseInt(indate.substr(0,4),10) , parseInt(indate.substr(4,2),10) - 1,parseInt(indate.substr(6,2),10) , 0,0,0);
	var yn = d.getFullYear();
	mn = d.getMonth();
	var dn = d.getDate();
	var d1 = new Date(yn,0,1,12,0,0); // noon on Jan. 1
	var d2 = new Date(yn,mn,dn,12,0,0); // noon on input date
	if (d2.dst()) summertime = 1; else summertime=0;
	var ddiff = Math.round((d2-d1)/864e5);
    return ddiff+1; 
}

function sqr(x)
{
	// square of x
	return x*x;
}

function sun_declination(doy)
{
	// declination of sun in radians
	// Formula 2008 by Arnold(at)Barmettler.com, fit to 20 years of average declinations (2008-2017)
	return 0.409526325277017*Math.sin(0.0169060504029192*(doy-80.0856919827619)); 
}

function timedifference(declination)
{
	// time from sunrise to highest point of sun
	return 12.0*Math.acos((Math.sin(h_sunrise) - Math.sin(latRAD)*Math.sin(declination)) / (Math.cos(latRAD)*Math.cos(declination)))/pi;
}

function timeequation(doy)
{
	//difference of real and average suntime
	// formula 2008 by Arnold(at)Barmettler.com, fit to 20 years of average equation of time (2008-2017)
	return -0.170869921174742*Math.sin(0.0336997028793971 * doy + 0.465419984181394) - 0.129890681040717*Math.sin(0.0178674832556871*doy - 0.167936777524864);
}
function sunrise(doy)
{
	var DC = sun_declination(doy);
	return 12 - timedifference(DC) - timeequation(doy);
}

function sunset(doy)
{
	var DC = sun_declination(doy);
	return 12 + timedifference(DC) - timeequation(doy);
}

function refraction(h0)
{
	// refraction in radians
	return RAD*0.061359*(0.1594+1.123*h0+0.065656*h0*h0)/(1+28.9344*h0+277.3971*h0*h0); 
}

function global_radiation(doy) {
	return 1367*(1 + 0.03344*Math.cos(2*pi*doy/365.25-0.048869));  
}

//source: http://re.jrc.ec.europa.eu/pvgis/solres/solmod3.htm
function direct_beam(h0ref){
	if (h0ref<-1.5*RAD) return 0;
	//elevation z=0
	var m = 1/(Math.sin(h0ref) + 0.50572*Math.pow(h0ref+6.07995,-1.6364)); 
	//var m = (1.002432*Math.pow(Math.sin(h0ref),2)+0.148386*Math.sin(h0ref)+0.0096467)/(Math.pow(Math.sin(h0ref),3)+0.149864*Math.pow(Math.sin(h0ref),2)+0.0102963*Math.sin(h0ref)+0.000303978);
	if (m <= 20) var dR = 1/(6.6296 + 1.7513 *m - 0.1202*Math.pow(m,2) + 0.0065*Math.pow(m,3) - 0.00013*Math.pow(m,4)); 
	else var dR = 1/(10.4 + 0.718* m);
	a= G0* Math.exp(-0.8662*LTF*m*dR);
	if (a>0) return a; else return 0; 
}

//source: http://re.jrc.ec.europa.eu/pvgis/solres/solmod3.htm
function diffuse_radiation(h0){
//	if (h0<-3.5*RAD) return 0;
	var Tn= -0.015843 + 0.030543*LTF + 0.0003797*Math.pow(LTF,2);
	var A1= 0.26463 - 0.061581*LTF + 0.0031408*Math.pow(LTF,2);
	if (Tn*A1<0.0022) A1=0.0022/Tn;
	var A2 = 2.04020 + 0.018945 *LTF - 0.011161 *Math.pow(LTF,2);
	var A3 = -1.3025 + 0.039231 *LTF + 0.0085079 *Math.pow(LTF,2);
	var Fd = A1 + A2* Math.sin(h0) + A3*Math.pow(Math.sin(h0),2);	
	var a=G0*Tn*Fd;
	if (a>0) return a; else return 0; 
}

function diffrad_coeff(B0c,Dhc,az,h0,beam_coeff) {
//	if (h0<-4*RAD) return 0;
	// better fit?
	var Kb = Dhc/G0; 
	// according to jrc:
	//var Kb = B0c/G0;
	var gn = (skydome_coeff+Math.cos(pv_roof))/2;
	//sunlit
	if (beam_coeff>0) {
	  //south Europe: var N = 0.00263-0.712*Kb-0.6883*Math.pow(Kb,2);
	  var N = 0.00333-0.415*Kb-0.6987*Math.pow(Kb,2);
	  var FgN = gn + (Math.sin(pv_roof)-pv_roof*Math.cos(pv_roof)-pi*Math.pow(Math.sin(pv_roof/2),2))*N;
	  if (h0>=0.1) {
		  //error jrc???
		  //var coeff = FgN*(1-Kb)+Kb*beam_coeff/Math.sin(h0);
		  var coeff = FgN*(1-Kb)+Kb*beam_coeff/Math.sin(h0);
	  } else {
		  ALN = az-pv_az;
		  if (ALN>pi) ALN = ALN - 2*pi;
		  if (ALN<-pi) ALN = ALN + 2*pi;
     	  var coeff = FgN*(1-Kb)+Kb*Math.sin(pv_roof)*Math.cos(ALN)/(0.1 - 0.008 *h0);
	  }
	//shadow  
	} else {
		// var N = 0.25227;	according to jrc, but doesn't match
		var N = 0.25227;
		// var N = 1.1;
		var coeff = gn + (Math.sin(pv_roof)-pv_roof*Math.cos(pv_roof)-pi*Math.pow(Math.sin(pv_roof/2),2))*N;
	}
//	tmp_debug =Kb;
	if (coeff>0) return coeff; else return 0;
};

function ground_reflection (B0c,Dhc,h0){
	var gn = (skydome_coeff+1)/2;
	var a=0.2*gn*(B0c*Math.sin(h0)+Dhc)*(1-Math.cos(pv_roof))/2;
	if (a>0) return a; else return 0;
}

function azimuthhight(curr_time)
{
	var cosdec = Math.cos(DC);
	var sindec = Math.sin(DC);
	var TimeSinceNoon = curr_time+longitude/15-time_zone -12 +TE;  // time in hours since sun is south
	var lha = TimeSinceNoon*(1.0027379-1/365.25)*15*RAD; // hour_angle of true noon in radians
	// 1.0027379: starcorrection, 1./365.25: rectacension of sun per day in degrees
	var coslha = Math.cos(lha);
	var sinlha = Math.sin(lha);
	var coslat = Math.cos(latRAD);
	var sinlat = Math.sin(latRAD);
	var N = -cosdec * sinlha;
	var D = sindec * coslat - cosdec * coslha * sinlat;
	var coor = new Object();
	coor.azimuth = Math.atan2(N, D); if (coor.azimuth<0) coor.azimuth += 2*pi; // azimuth: north=0, east=pi/2, west=3/4pi
	coor.h0  = Math.asin( sindec * sinlat + cosdec * coslha * coslat ); // hight of sun
	coor.h0ref  = coor.h0  + refraction(coor.h0);
	coor.beam_coeff = (Math.cos(coor.h0)*Math.sin(pv_roof)*Math.cos(pv_az-coor.azimuth)+Math.sin(coor.h0)*Math.cos(pv_roof));
    if (coor.beam_coeff<0) coor.beam_coeff=0; 
	coor.time=curr_time;
//	coor.am=airmass(coor.h0ref);
//	coor.old_intens=old_intensity(coor.am);
//    var mc= meas_correction(doy);
	coor.B0c=direct_beam(coor.h0ref);
	coor.Dhc=diffuse_radiation(coor.h0);
	if (coor.Dhc==0) coor.B0c=0;
	coor.gr=ground_reflection(coor.B0c,coor.Dhc, coor.h0);
	//in W/m2
	coor.Dhc_coeff=diffrad_coeff(coor.B0c,coor.Dhc,coor.azimuth,coor.h0,coor.beam_coeff);
	coor.ar_coeff=angular_reflection(coor.beam_coeff);
	//coor.ar_coeff=1;
	var tot_en = coor.B0c*coor.beam_coeff*coor.ar_coeff+coor.Dhc*coor.Dhc_coeff+coor.gr;
//	tot_en = tot_en*low_rad(tot_en);
	// in W/m2 , ca 0.1 indirect light
	coor.temp_panel = panel_temp (tot_en/1000,doy,curr_time);
	coor.temp_coeff=temperature_coeff (coor.temp_panel);
	coor.pan_coeff=panel_coeff (tot_en/1000*coor.temp_coeff);
    coor.gen_coeff = generator_coeff(tot_en/1000*coor.temp_coeff*coor.pan_coeff);
	coor.tot_en = tot_en*coor.temp_coeff*coor.pan_coeff*coor.gen_coeff;
    //in kW/m2

	coor.debug=tmp_debug;
//	coor.debug = Math.round(100*cf(coor.azimuth,coor.h0ref))/100;
    return coor;
}

function timeStringToFloat(time) {
	  var hoursMinutes = time.split(/[.:]/);
	  var hours = parseInt(hoursMinutes[0], 10);
	  var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
	  return hours + minutes / 60;
}

//summer time function
//call summer time by checking (example):
//var today = new Date();
//if (today.dst()) do_something;
Date.prototype.stdTimezoneOffset = function() {
var jan = new Date(this.getFullYear(), 0, 1);
var jul = new Date(this.getFullYear(), 6, 1);
return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
}
Date.prototype.dst = function() {
return this.getTimezoneOffset() < this.stdTimezoneOffset();
}

function daysInMonth(month,year) {
	var dd = new Date(year, month, 0);
	return dd.getDate();
} 
