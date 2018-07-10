#!/usr/bin/python

import os, re, sys
import mysql.connector
import requests
import classificate as cl


os.chdir("../../..")
parent = os.getcwd()
filename = parent + '/configuration.php'
host = ""
user = ""
db = ""
password = ""
with open(filename, "r") as f:
	for line in f:
		if "$host = '" in line :
			host = line.split('=')[1][:-2]
		elif "$user = '" in line:
			user = line.split('=')[1][:-2]
		elif "$db = '" in line:
			db = line.split('=')[1][:-2]
		elif "$password = '" in line:
			password = line.split('=')[1][:-2]

host=re.search(r'\w+', host).group()
user=re.search(r'\w+', user).group()
db=re.search(r'\w+', db).group()
password=re.search(r'\w+', password).group()
	

config = {
  'user': user,
  'password': password,
  'host':host,
  'port':'8889',
  'database': db,
  'raise_on_warnings': True,
}

cnx = mysql.connector.connect(**config)
cur=cnx.cursor()

sql = "select params from jos_extensions where name like 'com_emundus'"
cur.execute(sql)
data = cur.fetchall()

for i in data[0][0].split(","):
	if "applicant_files_path" in i:
		filepath = i.split('":"')[1][:-1].replace('\\', "")

# get photos
print "**********************************************************"
print "**************** PHOTOS **********************************"
print "**********************************************************"

sql="select eu.attachment_id, eu.user_id, eu.filename, sa.lbl, sa.ocr_keywords\
    from jos_emundus_uploads eu\
    left join jos_emundus_setup_attachments sa on eu.attachment_id = sa.id\
    where sa.lbl like '_photo' and eu.is_validated = -2"

cur.execute(sql)
data = cur.fetchall()
print "photos processing, please wait ..."
for d in data:
	if os.path.exists(parent+"/"+filepath+ str(d[1])+"/"+d[2]):
		valid = cl.main(parent+"/"+filepath+ str(d[1])+"/"+d[2], "isphoto")
		req = "UPDATE jos_emundus_uploads SET is_validated="+str(valid)+" WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
		cur.execute(req)
		cnx.commit()

print "end photos processing"

# get passports
print "**********************************************************"
print "**************** PASSPORTS *******************************"
print "**********************************************************"

sql="select eu.attachment_id, eu.user_id, eu.filename, sa.lbl, sa.ocr_keywords\
    from jos_emundus_uploads eu\
    left join jos_emundus_setup_attachments sa on eu.attachment_id = sa.id\
    where sa.lbl in ('_passport', '_identity') and eu.is_validated = -2"

cur.execute(sql)
data = cur.fetchall()

print "passports processing, please wait ..."

for d in data:
	if os.path.exists(parent+"/"+filepath+ str(d[1])+"/"+d[2]):
		if d[4]:
			valid = cl.main(parent+"/"+filepath+ str(d[1])+"/"+d[2], "ispassport", d[4])
		else:
			valid = cl.main(parent+"/"+filepath+ str(d[1])+"/"+d[2], "ispassport")

		req = "UPDATE jos_emundus_uploads SET is_validated="+str(valid)+" WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
		cur.execute(req)
		cnx.commit()
		# after sql insertion, remove generated jpeg image
		image = parent+"/"+filepath+ str(d[1])+"/"+d[2][:-4]+'.jpg'
		if os.path.exists(image):
			os.remove(image)
			
print "end passports processing"
