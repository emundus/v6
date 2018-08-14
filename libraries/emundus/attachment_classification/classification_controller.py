#!/usr/bin/python

import os, re, sys
import mysql.connector
import requests

import datetime
from time import gmtime, strftime, sleep
import tarfile
import socket
import progressbar
#import PyPDF2
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
  	'database': db
}

cnx = mysql.connector.connect(**config)
cur=cnx.cursor()

sql = "select params from jos_extensions where name like 'com_emundus'"
try:
	cur.execute(sql)
	data = cur.fetchall()
except:
	sys.exit(1)


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
    where sa.lbl like '_photo' and eu.is_validated IS NULL order by eu.timedate ASC limit 300"
try:
	cur.execute(sql)
	data = cur.fetchall()
except Exception, e:
	print 'An error occur, '+str(e)
	sys.exit(1)
	


print "photos processing, please wait ..."
ip = socket.gethostbyname(socket.gethostname())
status = ""
filename = parent+'/logs/com_emundus.classification.php'
if not os.path.exists(filename):
	open(filename, 'w+')

if os.path.exists(filename):
	fileyearweek = strftime("%Y%W", gmtime(os.path.getmtime(filename)))
	fileweek = 	strftime("%W", gmtime(os.path.getmtime(filename)))
	current_week = strftime("%W", gmtime())

	if current_week > fileweek:
		tar = tarfile.open(parent+'/logs/com_emundus.classification.'+ fileyearweek +'.php.tar.gz', "w:gz")
		os.chdir(parent+'/logs/')
		os.rename('com_emundus.classification.php','com_emundus.classification'+ fileyearweek +'.php')
		tar.add('com_emundus.classification'+ fileyearweek +'.php')
		tar.close()
		if os.path.exists('com_emundus.classification'+ fileyearweek +'.php'):
			os.remove('com_emundus.classification'+ fileyearweek +'.php')
			
		open(filename, 'w+')
	with open(filename, 'r+') as f:
		if not f.read(1):
			f.write('Datetime\t\tPriority\t\tClientip\t\tCategory\t\tType\t\tMessage\t\tStatus \n')
		
		#load a progressbar
		
		bar = progressbar.ProgressBar(maxval=len(data), widgets=[progressbar.Bar('=', '[', ']'), ' ', progressbar.Percentage()])
		bar.start()
		
		i = 0
		for d in data:
			i += 1
			if os.path.exists(parent+"/"+filepath+ str(d[1])+"/"+d[2]):
				try:
					valid = cl.isPhoto(parent+"/"+filepath+ str(d[1])+"/"+d[2])
				
					#insert log in log/com_emundus.classification.php
					if valid == 1:
						status = "success"
					if valid == 0:
						status = "failed"
				
					f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tVALIDATION\t\t'+ip+'\t\tcom_emundus\t\t PHOTO \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\t'+status+' \n')
					req = "UPDATE jos_emundus_uploads SET is_validated="+str(valid)+" WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
					try:
						cur.execute(req)
						cnx.commit()
					except Exception, e:
						print 'An error occur, '+str(e)
						sys.exit(1)
						
				except:
					f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tVALIDATION\t\t'+ip+'\t\tcom_emundus\t\t PHOTO \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\tfailed \n')
					req = "UPDATE jos_emundus_uploads SET is_validated=0 WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
					try:
						cur.execute(req)
						cnx.commit()
					except Exception, e:
						print 'An error occur, '+str(e)
						sys.exit(1)
						
			else:
				#insert log
				f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tERROR\t\t'+ip+'\t\tcom_emundus\t\t PHOTO \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\tFile not found\n')
			bar.update(i)
			sleep(0.2)
		bar.finish()
		del data
		print "end photos processing"
		
		# get passports
		print "**********************************************************"
		print "**************** PASSPORTS *******************************"
		print "**********************************************************"

		sql="select eu.attachment_id, eu.user_id, eu.filename, sa.lbl, sa.ocr_keywords, eau.firstname, eau.lastname, esc.end_date\
			from jos_emundus_uploads eu\
			left join jos_emundus_setup_attachments sa on eu.attachment_id = sa.id\
			left join jos_emundus_users eau on eu.user_id = eau.user_id\
			left join jos_emundus_setup_campaigns esc on eu.campaign_id = esc.id\
			where (sa.lbl like '_passport%' or sa.lbl like '_identity%') and eu.is_validated IS NULL order by eu.timedate ASC limit 300"

		try:
			cur.execute(sql)
			data = cur.fetchall()
		except Exception, e:
			print 'An error occur, '+str(e)
			sys.exit(1)

		print "passports processing, please wait ..."
		
		# load a progressbar
		bar = progressbar.ProgressBar(maxval=len(data), widgets=[progressbar.Bar('=', '[', ']'), ' ', progressbar.Percentage()])
		bar.start()
		
		i = 0
		for d in data:
			i += 1
			if os.path.exists(parent+"/"+filepath+ str(d[1])+"/"+d[2]):
				try:
					valid = cl.isPassport(parent+"/"+filepath+ str(d[1])+"/"+d[2], d[5], d[6], d[7])
					#insert log
					if valid == 1:
						status = "success"
					if valid == 0:
						status = "failed"
				
					f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tVALIDATION\t\t'+ip+'\t\tcom_emundus\t\t PASSPORT-ID_CARD \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\t'+status+' \n')
					req = "UPDATE jos_emundus_uploads SET is_validated="+str(valid)+" WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
					try:
						cur.execute(req)
						cnx.commit()
					except Exception, e:
						print 'An error occur, '+str(e)
						sys.exit(1) 
						

				except:
					f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tVALIDATION\t\t'+ip+'\t\tcom_emundus\t\t PASSPORT-ID_CARD \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\tfailed \n')
					req = "UPDATE jos_emundus_uploads SET is_validated=0 WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
					try:
						cur.execute(req)
						cnx.commit()
					except Exception, e:
						print 'An error occur, '+str(e)
						sys.exit(1)
						
								
			else:
				#insert log			
				f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\t ERROR \t\t'+ip+'\t\tcom_emundus\t\t PASSPORT-ID_CARD \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\tFile not found \n')
			
			# after sql insertion, remove generated jpeg image
			image = parent+"/"+filepath+ str(d[1])+"/"+d[2][:-4]+'.jpg'
			if os.path.exists(image):
				os.remove(image)
				
			bar.update(i)
			sleep(0.2)
		bar.finish()
		del data
		print "end passports processing"
	
		# get CV
		print "**********************************************************"
		print "**************** CV **************************************"
		print "**********************************************************"

		sql="select eu.attachment_id, eu.user_id, eu.filename, sa.lbl, sa.ocr_keywords\
			from jos_emundus_uploads eu\
			left join jos_emundus_setup_attachments sa on eu.attachment_id = sa.id\
			where sa.lbl like '_cv%' and eu.is_validated IS NULL order by eu.timedate ASC limit 300"

		try:
			cur.execute(sql)
			data = cur.fetchall()
		except Exception, e:
			print 'An error occur, '+str(e)
			sys.exit(1)
			
		print "CV processing, please wait ..."
		
		# load a progressbar
		bar = progressbar.ProgressBar(maxval=len(data), widgets=[progressbar.Bar('=', '[', ']'), ' ', progressbar.Percentage()])
		bar.start()
		
		i = 0
		for d in data:
			i += 1
			if os.path.exists(parent+"/"+filepath+ str(d[1])+"/"+d[2]):
				try:
					if d[4]:
						valid = cl.isCv(parent+"/"+filepath+ str(d[1])+"/"+d[2], d[4])
					else:
						valid = cl.isCv(parent+"/"+filepath+ str(d[1])+"/"+d[2])
					#insert log

					if valid == 1:
						status = "success"
					if valid == 0:
						status = "failed"
					
					f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tVALIDATION\t\t'+ip+'\t\tcom_emundus\t\t CV \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\t'+status+' \n')
					req = "UPDATE jos_emundus_uploads SET is_validated="+str(valid)+" WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
					try:
						cur.execute(req)
						cnx.commit()
					except Exception, e:
						print 'An error occur, '+str(e)
						sys.exit(1)

					# after sql insertion, remove generated jpeg image
					image = parent+"/"+filepath+ str(d[1])+"/"+d[2][:-4]+'.jpg'
					if os.path.exists(image):
						os.remove(image)

				except:
					f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tVALIDATION\t\t'+ip+'\t\tcom_emundus\t\t CV \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\tfailed \n')
					req = "UPDATE jos_emundus_uploads SET is_validated=0 WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
					try:
						cur.execute(req)
						cnx.commit()
					except Exception, e:
						print 'An error occur, '+str(e)
						sys.exit(1)
						
				
					
			else:
				#insert log		
				f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\t ERROR\t\t'+ip+'\t\tcom_emundus\t\t CV \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\tFile not found \n')
			
			bar.update(i)
			sleep(0.2)
		bar.finish()
		del data
		print "end CV processing"
		
		# get motivation
		print "**********************************************************"
		print "**************** MOTIVATIONS *****************************"
		print "**********************************************************"

		sql="select eu.attachment_id, eu.user_id, eu.filename, sa.lbl, sa.ocr_keywords\
			from jos_emundus_uploads eu\
			left join jos_emundus_setup_attachments sa on eu.attachment_id = sa.id\
			where sa.lbl like '_motivation%' and eu.is_validated IS NULL order by eu.timedate ASC limit 300"

		try:
			cur.execute(sql)
			data = cur.fetchall()
		except Exception, e:
			print 'An error occur, '+str(e)
			sys.exit(1)
			

		print "motivation processing, please wait ..."
		
		# load a progressbar
		bar = progressbar.ProgressBar(maxval=len(data), widgets=[progressbar.Bar('=', '[', ']'), ' ', progressbar.Percentage()])
		bar.start()
		
		i = 0
		for d in data:
			i += 1
			if os.path.exists(parent+"/"+filepath+ str(d[1])+"/"+d[2]):
				try:
					if d[4]:
						valid = cl.isMotivation(parent+"/"+filepath+ str(d[1])+"/"+d[2], d[4])
					else:
						valid = cl.isMotivation(parent+"/"+filepath+ str(d[1])+"/"+d[2])
					#insert log
					if valid == 1:
						status = "success"
					if valid == 0:
						status = "failed"

					f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tVALIDATION\t\t'+ip+'\t\tcom_emundus\t\t MOTIVATION \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\t'+status+' \n')
					req = "UPDATE jos_emundus_uploads SET is_validated="+str(valid)+" WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
					try:
						cur.execute(req)
						cnx.commit()
					except Exception, e:
						print 'An error occur, '+str(e)
						sys.exit(1)
						

					# after sql insertion, remove generated jpeg image
					image = parent+"/"+filepath+ str(d[1])+"/"+d[2][:-4]+'.jpg'
					if os.path.exists(image):
						os.remove(image)

				except:
					f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\tVALIDATION\t\t'+ip+'\t\tcom_emundus\t\t MOTIVATION \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\tfailed \n')
					req = "UPDATE jos_emundus_uploads SET is_validated=0 WHERE filename LIKE '"+d[2]+"' AND user_id = "+str(d[1])
					try:
						cur.execute(req)
						cnx.commit()
					except Exception, e:
						print 'An error occur, '+str(e)
						sys.exit(1)
					
			else:
				#insert log			
				f.write(strftime("%Y-%m-%d %H:%M:%S", gmtime()) +'\t\t ERROR\t\t'+ip+'\t\tcom_emundus\t\t MOTIVATION \t\t'+ parent+"/"+filepath+ str(d[1])+"/"+d[2] +'\t\tFile not found \n')
			
			bar.update(i)
			sleep(0.2)
		bar.finish()
		del data
		print "end motivation processing"
	f.close()
	