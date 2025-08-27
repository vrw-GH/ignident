@ECHO OFF
@ECHO IGNIDENT lastgood Backup

CD .\livesite.bak
::mysqldump --host="db739296878.db.1and1.com" --user="dbo739296878" --password="RxbHprPIVOENQoIFCFkK" db739296878 > temp.sql
for %%A in (temp.sql) do (
   if %%~zA EQU 0 (
      del temp.sql
      Echo "LIVE DB Backup failed!!"
      PAUSE
   ) else (
      S:\GnuWin32\bin\gzip temp.sql 
      rename temp.sql.gz db739296878_%DATE%.sql.gz
   )
)

CD ..\lastgood.bak
mysqldump --host="localhost" --user="vwDev" --password="vwDev!DB7175" db739296878 > temp.sql
for %%A in (temp.sql) do (
   if %%~zA EQU 0 (
      del temp.sql
      Echo "LIVE DB Backup failed!!"
   ) else (
      S:\GnuWin32\bin\gzip temp.sql 
      rename temp.sql.gz db739296878_%DATE%_%TIME:~0,2%%TIME:~3,2%.sql.gz
   )
)

PAUSE