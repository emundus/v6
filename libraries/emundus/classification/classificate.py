#!/usr/bin/python

from PIL import Image
import os, os.path, sys, re
from pytesseract import image_to_string
import cv2
import face_recognition


Image.MAX_IMAGE_PIXELS = 1000000000  

# convert pdf file to jpeg image
def pdf2image(pdfPath, dpi):
	pages = convert_from_path(pdfPath, dpi=dpi, output_folder=None, first_page=1, last_page=1, fmt='jpg')
	filename = pdfPath[:-4]+'.jpg'
	for page in pages:
		page.save(filename, 'JPEG')
	return filename

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
	#if len(image.shape) == 3:
	#	image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

	faces = detectFaces(image)

	
	if len(faces) > 0 and getString(image) == "":
		return 1
	else:
		return 0
	
	
# is image a passport?
def isPassport(imagePath, keywords = ""):
	# Read the image
	image = cv2.imread(imagePath)

	faces = detectFaces(image)
	#print "Found {0} faces!".format(len(faces))

	if len(image.shape) == 3: # if color image
		image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY) #convert to grayscale image

	#averaging filter 9*9 to remove gaussian noisy(5*)
	blur = cv2.GaussianBlur(image,(9,9),0)
	blur = cv2.GaussianBlur(blur,(9,9),0)
	blur = cv2.GaussianBlur(blur,(9,9),0)
	blur = cv2.GaussianBlur(blur,(9,9),0)
	blur = cv2.GaussianBlur(blur,(9,9),0)
	#image binarisation using gaussian threshold 
	image = cv2.adaptiveThreshold(blur,255,cv2.ADAPTIVE_THRESH_GAUSSIAN_C,\
          cv2.THRESH_BINARY,11,2)

	#cv2.imshow("binary", image)
	#cv2.waitKey(0)

	char = getString(image)
	match = re.search(r'\w+[<]+\w+', char)
	keymatch = []
	if keywords:
		key_t = keywords.split(";")
		matchkeywords = [re.compile(f ,re.I) for f in key_t]
		keymatch = [m.findall(char) for m in matchkeywords if m.findall(char)]
	
	if len(faces) > 0 and match and keymatch:
		return 1
	elif (len(faces) > 0  and keymatch) or (len(faces) > 0  and match):
		return 0.75
	elif (match):
		return 0.5
	else:
		return 0

# is image a cv?
def isCv(imagePath):
	# Read the image
	image = cv2.imread(imagePath)
	if len(image.shape) == 3:
		image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
	if 'Curriculum vitae' in getString(image):
		if 'europass' in getString(image):
			return 100
		else:
			return 70
	else:
		return 0
	
	
def main(image, function, keywords=""):

	if function == "isphoto":
		if image.endswith('.pdf'):
			image = pdf2image(image, 250)
		if image.endswith('.gif'):
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
		if image.endswith('.pdf'):
			image = pdf2image(image, 350)
		res = isPassport(image, keywords)
		if res == 1:
			print 'yes, it''s a passeport 100%'
			return 1
		elif res == 0.75:
			print 'yes, it''s a passeport 75%'
			return 0.75
		elif res == 0.5:
			print 'yes, it''s a passeport 50%'
			return 0.5
		else:
			print 'no, it''s not a passport'
			return 0

	if function == "iscv":
		if image.endswith('.pdf'):
			image = pdf2image(image, 250)
		res = isCv(image)
		if res == 100:
			print 'yes it'' a cv in 100% !'
		elif res == 70:
			print 'yes it'' a cv in 70% !'
		else:
			print 'no, it''s not a cv !'




