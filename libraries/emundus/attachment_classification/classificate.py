#!/usr/bin/python

from PIL import Image
import os, os.path, sys, re
from pytesseract import image_to_string
import cv2
from pdf2image import convert_from_path
import face_recognition

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
	
# get generated string by tesseract ocr
def getString(image):
	char = image_to_string(image)
	return char 

# is image a human photo? 
def isPhoto(imagePath):
	# Read the image
	image = cv2.imread(imagePath)
	if len(image.shape) == 3:
		image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

	faces = detectFaces(image)
	#print getClassResult(image)

	if (len(faces) > 0 and getClassResult(image) == "photo") or (len(faces) > 0 and getString(image) == ""):
		return 1
	else:
		return 0
	
	
# is image a passport?
def isPassport(imagePath, keywords = ""):
	# Read the image
	image = cv2.imread(imagePath)
	faces = detectFaces(image)
	height, width = image.shape[:2]
	if height >= 3000 and width >= 3000:
		image = cv2.resize(image, (height / 3, width / 3)) 
	

	if len(image.shape) == 3: # if color image
		image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY) #convert to grayscale image
	classresult = getClassResult(image)
	#print "Found {0} faces!".format(len(faces))

	#averaging filter 9*9 to remove gaussian noisy(5*)
	blur = cv2.GaussianBlur(image,(9,9),0)
	blur = cv2.GaussianBlur(blur,(9,9),0)
	blur = cv2.GaussianBlur(blur,(9,9),0)
	blur = cv2.GaussianBlur(blur,(9,9),0)
	blur = cv2.GaussianBlur(blur,(9,9),0)
	#image binarisation using gaussian threshold 
	image = cv2.adaptiveThreshold(blur,255,cv2.ADAPTIVE_THRESH_GAUSSIAN_C,\
          cv2.THRESH_BINARY,11,2)

	char = getString(image)
	#char = "ezgcbzv<brkzbfnfyhgbv"
	match = re.search(r'\w+[<]+\w+', char)
	keymatch = []
	if keywords:
		key_t = keywords.split(";")
		matchkeywords = [re.compile(f ,re.I) for f in key_t]
		keymatch = [m.findall(char) for m in matchkeywords if m.findall(char)]

	if (len(faces) > 0 and match and keymatch) or (len(faces) > 0  and keymatch) or (len(faces) > 0 and classresult == "passport") or (match and classresult == "passport") or (keymatch and classresult == "passport") or (match and len(faces) > 0 ):
		return 1
	#elif (len(faces) > 0  and keymatch) or (len(faces) > 0  and match):
		#return 0.75
	#elif (match):
		#return 0.5
	else:
		return 0

	
def main(image, function, keywords=""):
	if function == "isphoto":
		if image.lower().endswith('.pdf'):
			image = pdf2image(image, 250)
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
			#print 'yes, it''s a photo'
			return 1
		else:
			#print 'no, it''s not a photo'
			return 0

	if function == "ispassport":
		if image.lower().endswith('.pdf'):
			image = pdf2image(image, 350)

		
		res = isPassport(image, keywords)
		if res == 1:
			#print 'yes, it''s a passeport 100%'
			return 1
		#elif res == 0.75:
			#print 'yes, it''s a passeport 75%'
			#return 0.75
		#elif res == 0.5:
			#print 'yes, it''s a passeport 50%'
			#return 0.5
		else:
			#print 'no, it''s not a passport'
			return 0





