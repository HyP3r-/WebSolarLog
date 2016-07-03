version = "v1.00"
import sys
import serial
import datetime
import locale
 
###############################################################################################################
# Main program
###############################################################################################################
#Initialize
p1_telegram  = False
p1_timestamp = ""
p1_teller    = 0
p1_log       = True
 
#Set COM port config
ser          = serial.Serial()
ser.baudrate = 9600
ser.bytesize = serial.SEVENBITS
ser.parity   = serial.PARITY_EVEN
ser.stopbits = serial.STOPBITS_ONE
ser.xonxoff  = 0
ser.rtscts   = 0
ser.timeout  = 1
ser.port     = sys.argv[1]
 
 
#Open COM port
try:
    ser.open()
except:
    sys.exit ("Fout bij het openen van poort %s. "  % ser.name)     
 
while p1_log:
    p1_line = ''
    try:
        p1_raw = ser.readline()
    except:
        sys.exit ("Fout bij het lezen van poort %s. " % ser.name )
        ser.close()
 
    p1_str  = p1_raw
    p1_str  = str(p1_raw, "utf-8")
    p1_line = p1_str.strip()
    print (p1_line)
 
    if p1_line[0:1] == "/":
        p1_telegram = True
    if p1_line[0:1] == "!":
        p1_log      = False

#Close port and show status
try:
    ser.close()
except:
    sys.exit ("Fout bij het sluiten van %s." % ser.name )
