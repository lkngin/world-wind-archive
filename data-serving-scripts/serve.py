# simple python worldwind tile server, v 1.0 2006
# author: Frank Kuehnel (RIACS)
 
from mod_python import apache
import string
import errno

#todo: make more robust + handle various datasets

datasetBasename = '/Users/admin/apollo/'

def request_error(req):
	req.content_type = 'text/plain'
	req.send_http_header()
	req.write('Invalid WorldWind tile request\n')

def handler(req):
	global datasetBasename	

	# we want to have parameters at least
	if req.args:
		args = req.args
	else:
		request_error(req)
		return apache.OK
		
	args = req.args
	for elem in args.split('&'):
		key   = elem[0:2]
		value = elem[2:]
		if key == 'T=':
			dataSet = value
		elif key == 'L=':
			level = value
		elif key == 'X=':
			column = value
		elif key == 'Y=':
			row = value

	# check parameter
	try:
		levelNum = string.atoi(level)
		colNum = string.atoi(column)
		rowNum = string.atoi(row)
	except ValueError:
		request_error(req)
		return apache.OK

	# load image file
	try:
		filepath = '%u/%04u/' % (levelNum, rowNum)
		filename = '%04u_%04u.jpg' % (rowNum, colNum)
		fullname = datasetBasename + filepath + filename
		input = open(fullname,'rb')
		data  = input.read()
		input.close()
	except IOError, err:
		if err.errno == errno.ENOENT:
                        req.content_type = 'text/plain'
			req.send_http_header()
                        req.write('image tile ' + fullname + ' not available')
                        return apache.OK

	# output image
	req.content_type = 'image/jpeg'
	req.send_http_header()
	req.write(data)

	return apache.OK

