#!/usr/bin/python

from PIL import Image
import commands, os, os.path, sys, re
from pytesseract import image_to_string
import cv2
from pdf2image import convert_from_path
import face_recognition
from scipy import ndimage
import numpy as np
import textract
from passporteye.mrz.image import read_mrz
from alyn import Deskew
from time import gmtime, strftime, sleep
import difflib

import mahotas as mt
from sklearn.svm import LinearSVC

import training_vector as t_vector


Image.MAX_IMAGE_PIXELS = 1000000000  

# convert pdf file to jpeg image
def pdf2image(pdfPath, dpi):
	pages = convert_from_path(pdfPath, dpi=dpi, output_folder=None, first_page=1, last_page=1, fmt='jpg')
	filename = pdfPath[:-4]+'.jpg'
	for page in pages:
		page.save(filename, 'JPEG')
	return filename

def extract_features(image):
	# calculate haralick texture features for 4 types of adjacency
	textures = mt.features.haralick(image)

	# take the mean of it and return it
	ht_mean = textures.mean(axis=0)
	return ht_mean

def getClassResult(image):
	features = extract_features(image)
	clf_svm = LinearSVC(random_state=9)
	clf_svm.fit(t_vector.train_features(), t_vector.train_labels())
	# evaluate the model and predict label
	prediction = clf_svm.predict(features.reshape(1, -1))[0]
	return prediction

# face detector
def detectFaces(image):
	faces = face_recognition.face_locations(image)
	return faces

# deskew passport in order to extract correctly the mrz
def deskew(filename):
	image = cv2.imread(filename)
	image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY) #convert to grayscale image
	cv2.imwrite(filename,image)
	d = Deskew(
		input_file=filename,
		display_image=None,
		output_file=filename,
		r_angle=0)
	d.run()

# get generated string by tesseract ocr
def getString(image):
	char = image_to_string(image)
	#,config='-c tessedit_char_whitelist=<0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz -psm 6'
	return char 

def getText(pdf):
	text = textract.process(pdf)
	return text

def getSimilarity(w1, w2):
	seq = difflib.SequenceMatcher(None,w1,w2)
	d = seq.ratio()*100
	return d

def getMrz(filename):
	mrz = read_mrz(filename, save_roi=True)
	return mrz.to_dict()
	
def fix_incorrect_orientation(filename):  
    tesseractResult = str(commands.getstatusoutput('tesseract ' + filename + ' -  -psm 0'))
    #print('tesseractResult: ' + tesseractResult)
    regexObj = re.search("Orientation in degrees:\s([0-9])+",tesseractResult)
    if regexObj:
        orientation = int(regexObj.group().split(': ')[1])
        #print('orientation: ' + str(orientation))
        if orientation:
            image = cv2.imread(filename)
            image = ndimage.rotate(image, orientation)
            cv2.imwrite(filename, image)

# is image a human photo? 
def isPhoto(imagePath):
	# Read the image
	image = cv2.imread(imagePath)
	
	faces = detectFaces(image)

	if len(image.shape) == 3:
		image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

	if (len(faces) > 0 and getClassResult(image) == "photo") or (len(faces) > 0 and image_to_string(image) == ""):
		return 1
	else:
		return 0
	
	
# is image a passport?
def isPassport(imagePath, fname, lname, end_date):

	if imagePath.lower().endswith('.pdf'):
		imagePath = pdf2image(imagePath, 300)
	try:
		passdata = getMrz(imagePath)
	except:
		fix_incorrect_orientation(imagePath)
		# Read the image
		image = cv2.imread(imagePath)
		
		height, width = image.shape[:2]
		if height >= 6000 and width >= 6000:
			image = cv2.resize(image, (height / 3, width / 3)) 
			
		cv2.imwrite(imagePath, image)

		# deskew image
		deskew(imagePath)
		fix_incorrect_orientation(imagePath)
		passdata = getMrz(imagePath)

	#print passdata
	pass_names = passdata['names'].upper() +' '+ passdata['surname'].upper()
	form_names = fname.upper() +' '+ lname.upper()
	names_similarity = getSimilarity(pass_names, form_names)
	lname_similarity = getSimilarity(lname.upper(), passdata['surname'].upper())
	if (names_similarity >= 75 and int(passdata['expiration_date']) > int(end_date.strftime("%y%m%d"))) \
		or (fname.upper() in pass_names and lname.upper() in pass_names and int(passdata['expiration_date']) > int(end_date.strftime("%y%m%d")))\
		or lname_similarity >= 75 and fname.upper() in pass_names and int(passdata['expiration_date']) > int(end_date.strftime("%y%m%d")) :
		return 1
	else:
		return 0
	

# is image a cv?
def isCv(filepath, keywords = ""):
	text = ""
	path = os.getcwd()
	if filepath.lower().endswith('.pdf'):
		filepath = pdf2image(filepath, 300)
		image = cv2.imread(filepath)
		text = getString(image)
	if filepath.lower().endswith('.docx'):
		text = getText(filepath)
	if filepath.lower().endswith('.doc'):
		os.system('cat '+ filepath+ ' > ' + path + '/extractedtext.txt')
		text = open(path + '/extractedtext.txt', 'r').read()
		if os.path.exists(path + '/extractedtext.txt'):
			os.remove(path + '/extractedtext.txt')

	keymatch = []
	if keywords:
		key_t = keywords.split(";")
		matchkeywords = [re.compile(f ,re.I) for f in key_t]
		keymatch = [m.findall(text.lower()) for m in matchkeywords if m.findall(text.lower())]
	if keymatch:
		return 1
	else:
		return 0

def isMotivation(filepath, keywords = ""):
	path = os.getcwd()
	if filepath.lower().endswith('.docx') or filepath.lower().endswith('.pdf'):
		text = getText(filepath)
	if filepath.lower().endswith('.doc'):
		os.system('cat '+ filepath+ ' > ' + path + '/extractedtext.txt')
		text = open(path + '/extractedtext.txt', 'r').read()
		if os.path.exists(path + '/extractedtext.txt'):
			os.remove(path + '/extractedtext.txt')
	
	keymatch = []
	if keywords:
		key_t = keywords.split(";")
		matchkeywords = [re.compile(f ,re.I) for f in key_t]
		keymatch = [m.findall(text.lower()) for m in matchkeywords if m.findall(text.lower())]
	if keymatch:
		return 1
	else:
		return 0

def main(image, function, keywords=""):
	if function == "isphoto":
		if image.lower().endswith('.pdf'):
			image = pdf2image(image, 300)
		if image.lower().endswith('.gif'):
			im = Image.open(image)
			img = im.convert('RGB')
			pix = img.load()
			for y in range(img.size[1]):
				for x in range(img.size[0]):
					if pix[x, y][0] < 102 or pix[x, y][1] < 102 or pix[x, y][2] < 102:
						pix[x, y] = (0, 0, 0, 255)
					else:
						pix[x, y] = (255, 255, 255, 255)

			img.save(image[:-4]+'.jpg')
			image = image[:-4]+'.jpg'

		res = isPhoto(image)

		if res == 1:
			return 1
		else:
			return 0

	if function == "ispassport":
		if image.lower().endswith('.pdf'):
			image = pdf2image(image, 350)

		res = isPassport(image, uid)
		if res == 1:
			return 1
		else:
			return 0
	
	if function == "iscv":
		res = isCv(image, keywords)
		if res == 1:
			return 1
		else:
			return 0

	if function == "ismotivation":
		res = isMotivation(image, keywords)
		if res == 1:
			return 1
		else:
			return 0




