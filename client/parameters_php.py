#Path of the documents you wish to upload
path = "documents_for_www"
drive = "c:\\"

#username and password needed to delete and update files 
username="your username"
password="your password"

#where to upload the files change xxxxxxxxxx with your username
url_to_receive = 'http://users.sch.gr/xxxxxxxxx/files_www/upload.php'

filenames_excluded = ['httpserver.log', 'server_files.json']
#server side script residing url
server_name = 'users.sch.gr/xxxxxxxx' 

#seconds between folder scanning
seconds_to_pause = 15

my_work_dir = '/files_www/files_to_transfer/'
