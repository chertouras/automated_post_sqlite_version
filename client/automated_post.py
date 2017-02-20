#
#publish_files.py: A python script to automatically
#scan, hash, upload and delete files in a internet server folder.
#Τhis version of the script is allowing instant
#publication of files in a connected webpage through the HTTP POST.
#Php code exists in the server side to allow the publication.
#This is NOT a standalone script.
#
#Developed by: Konstantinos Chertouras - chertour@gmail.com


#module for file and directory scanning and access error codes
import os
import errno
#module for md5 hash function
import hashlib
#module for sys.exit (mainly for debugging purposes)
import sys
#module for parsing the server response
import ast
#module for allowing the sleep of program from a predifined interval
import time
#module for allowing send iso encoded letters correctly
import urllib
#allow logging of print statements
import logging
#module for enabling the POST http requests from within the program
import requests
#import path parameter from parameters_php.py configuration file
from parameters_php_for_sch import path, username, password, seconds_to_pause, drive
#do not download system files or files not created by user or whatever you want
from parameters_php_for_sch import filenames_excluded
#where is your server side code?
from parameters_php_for_sch import server_name, url_to_receive



#####Let's rock n roll###

# Store location of where the script lives

dir_path_script = os.path.dirname(os.path.realpath(__file__))
#print (dir_path)

########################################################################
################## logging all output to file       ####################
########################################################################



log = logging.getLogger('stdout')

class StreamLogger(object):
    """Logger implamantation"""
    def __init__(self, stream, prefix=''):
        self.stream = stream
        self.prefix = prefix
        self.data = ''

    def write(self, data):
        self.stream.write(data)
        self.stream.flush()

        self.data += data
        tmp = str(self.data)
        if '\x0a' in tmp or '\x0d' in tmp:
            tmp = tmp.rstrip('\x0a\x0d')
            log.info('%s%s' % (self.prefix, tmp))
            self.data = ''

    def flush(self):
        pass

logging.basicConfig(level=logging.INFO,
                    filename='text.log',
                    filemode='a')

sys.stdout = StreamLogger(sys.stdout, '[stdout] ')

###################################################################################
###################################################################################
###################################################################################


while True:
    print("===================================================================")
    print("Hello! Will check if the directory which holds the local files ", path, " exists ...")
    os.chdir(drive)
    result = (os.path.isdir(path))
    if result:
        print("Directory ", path, "exists... ")
        print("Current working directory now is ...", os.getcwd())
        print("Will cd to ", path, ' ')
        os.chdir(path)
        path = os.getcwd()
        print("Current working directory now is ...", os.getcwd())
        break
    else:
        print("A directory", path, "will be created...")
        os.mkdir(path)
        result = (os.path.isdir(path))
        if result:
            print("Directory ", path, " now exists... ")
            os.chdir(path)
            path = os.getcwd()
            print("Current working directory now is ...", os.getcwd())
            break
        else:
            print("There was an error with the process of creating a directory")
            print("Please create the directory ", path, " manually and run the script again")
            print("Exiting...")
            sys.exit()

#Already in path . Ready to scan for the files
#Get list of filenames in the directory defined in the path variable
#utf - 8 is supported natively


while True:
    print("===================================================================")

    #Parse filenames and replace spaces with underscores to allow easier cooperation with http
    try:
        for file in os.listdir(path):
            underscored_name = file.replace(" ", "_")
            os.rename(file, underscored_name)
    # in case you have created the same file but with underscores . Highly unlikely in normal usage
    except OSError as e:
        print("The following error was generated ", e)

        if e.errno == errno.EACCES:
            print('Please close all open files since the program tries to operate on them ')
            print('Program will continue although and post the file with the last saved data ')

        elif e.errno == errno.EEXIST:
            print("Application converts automatically whitespaces to underscores in filenames")
            print("Maybe you have already created a file with the same name",
                  "and with underscores entered instead of spaces")
            print("Please rename the filename you are trying to save  ")
            print("The program will exit to avoid further problems.",
                  "Please rename the one of the two files")
            sys.exit()
        else:
            print('There was an error with the file operations. Will continue ')
        
    #create the filelist in local directory except the excluded and the files starting with dot

    files = list(file for file in os.listdir(path)
                 if os.path.isfile(os.path.join(path, file)) and file not in filenames_excluded and
                 file[0] != '.' and file[0] != '~')

    
    #get the file hash (md5) in conjuction with the filename
    #changing filename or the file contents will produce a new hash
    def hash_files(filename_):
        """get the file hash (md5) in conjuction with the filename"""
        hash_object = hashlib.md5(open(filename_, 'rb').read())
        hash_object.update(filename_.encode('utf-8'))
        return hash_object.hexdigest()


    #create the hash list of hash values of files + filenames
    hash_list = list(map(hash_files, files))
    assert len(hash_list) == len(files)
    #create a dictionary with local files and hashes - useful for debugging
    local = {k:v for (k, v) in zip(hash_list, files)}
    #convert the list to a set to apply some relational algebra later
    local_hash_set = set(hash_list)
    #we have the server files hashes list ready to be filled
    server_hash_list = []



    # Connect to server and make a request from sch.gr for:
    # 1. Preparation of the server list
    # 2. Hash of the list
    # 3. Send back as a json response
    try:
        print("Connecting to sch.gr server ", server_name)
        payload = {'username': username, 'password': password}
        r = requests.post('http://'+server_name+'/files_www/server_file_list.php', payload)
        print("Connected to server. Generating server hash list...")
    #Parse the dictionary and get the server file list

        if r.text == "Unauthorized access":
            print("You have supplied wrong credentials. Please correct username",
                  "/ password and run the program again")
            print("Exiting ...")
            break
        else:
            print('Hash list retrieved...Ready to parse')
            server_files = ast.literal_eval(r.text)

    #Extract the hashes in order to compare them with the local hashes
        server_hash_list = list(server_files)
    #convert the list to a set to apply some relational algebra later
        server_hash_set = set(server_hash_list)
    #Now which hashes -> files should I upload and delete. Apply elementary set theory...
        delete_set = server_hash_set - local_hash_set
        upload_set = local_hash_set - server_hash_set
    except requests.exceptions.RequestException as e:
        print("There was an error. Please check if server is running... ")
        print('Detailed error message:  ', e)
        print("Is the Web Server or the underlying TCP connection availiable?")
        print("Will try again in ", seconds_to_pause, " seconds")
        time.sleep(seconds_to_pause)
        continue

    #Preparing to POST file
    try:

     #If connected first delete the files

        if delete_set:
            print('Ready to delete files from server')
            for hash_index in delete_set:
                payload = {'username': username, 'password': password, 'hash_filename':hash_index}
                r = requests.post('http://'+server_name+'/files_www/remote_del.php', payload)
                print('Deleted from server file :' + r.text)
        else:
            print('Nothing to delete. Continuing on upload ...')

    #Then upload the files

        if upload_set:
            print('Ready to upload files to server')
            print('Issuing a change directory to enter /files_www/files_to_tranfer')

            for hash_index in upload_set:
                filename = local[hash_index]
                payload = {'username': username, 'password': password,
                           'timestamp': os.path.getmtime(filename),
                           'size':os.path.getsize(filename)}
                print('Checking if local file exceeds maximum upload limit :' + filename)
                print('Contacting server: Local file size is:', os.path.getsize(filename))
                r = requests.post(url_to_receive, payload)
                if r.text == 'Exceeded filesize limit.':
                    print("Server Responded : ", r.text,
                          "File is not uploaded since it is larger than the maximun size allowed")
                    print("Program will continue to the next file, if an upload is pending...")
                    continue
                payload = {'username': username, 'password': password, 'hash' : hash_index,
                           'timestamp': os.path.getmtime(filename)}
                print('Opening local file to transfer through HTTP :' + filename)
                #handler for file
                file_handler = open(filename, 'rb')
                files = {'file': (urllib.parse.quote(filename, encoding='ISO-8859-7'),
                                  file_handler)}
                r = requests.post(url_to_receive, payload, files=files)
                if r.text == 'Unauthorized access':
                    print("Server Responded : ", r.text,
                          " You may have to check your username / password")
                    break
                elif r.text == 'Exceeded filesize limit.':
                    print("Server Responded : ", r.text,
                          "File is not uploaded since it is larger than the maximun size allowed")
                    print("Program will continue to the next file if an upload pending...")
                    file_handler.close()
                    continue
                elif r.text == 'No double extensions or unsafe filenames with dots allowed for security reasons.':
                    print("Server Responded : ", r.text,
                          "File is not uploaded. No filenames with double on no extension are \
                           allowed. Consider renaming the file or not uploading it at all...")
                    print("Program will continue to the next file if an upload pending...")
                    file_handler.close()
                    continue
                elif r.text == 'This type of files is not allowed to upload.':
                    print("Server Responded : ", r.text,
                          "File is not uploaded since it's extension is not in the \
                           in the extensions whitelist defined in server parameters.")
                    print("Program will continue to the next file if an upload pending...")
                    file_handler.close()
                    continue
                else:
                    print("File uploaded")
                    #release file from python process
                    file_handler.close()
            print('Program will scan again in ', seconds_to_pause, ' seconds')
        else:
            print('Nothing to upload ...')
            print('Bye for now... Will scan again in ', seconds_to_pause, 'seconds')


    except requests.exceptions.RequestException as e:
        print("There was an error. Please check if server is running... ")
        print('Detailed error message:  ', e)
        print("Is the Web Server or the underlying TCP connection availiable?")
        print("Will try again in ", seconds_to_pause, " seconds")
        time.sleep(seconds_to_pause)
        continue
    time.sleep(seconds_to_pause)