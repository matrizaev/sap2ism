#!/usr/bin/env python3
import re
import sys

duplicates = ['710020180122155730', '710020170511132125', '720020181010164040', '760020190405165625', '710020200327095712', '760020210209144275']

print(sys.argv[1])

with open(sys.argv[1], 'r', encoding='utf-8') as f:
		data = f.read()
		data = re.sub('\r|\n', '', data)
		data = re.sub(r'<description>(.*?)</description>', r'<description><![CDATA[\1]]></description>', data)
		data = re.sub(r'<(change|cancel)>\d{4}</(change|cancel)>', '', data)
		data = re.sub(r'<validity></validity>', '', data)
		data = re.sub(r'<file_description></file_description>', r'<file_description>Файл</file_description>', data)
		data = re.sub(r'<file_description>[^<]{50,}</file_description>', r'<file_description>Файл</file_description>', data)
		data = re.sub(r'<file_name>(\d{20})\.ПРИЛОЖ</file_name>', r'<file_name>\1.PDF</file_name>', data)
		data = re.sub(r'<document(?:(?!<file>).)+?</document>','',data)
#		data = re.sub(r'<document id=\"({})\">.+?</document>'.format('|'.join(duplicates)), '', data)
		with open(sys.argv[1], 'w', encoding = 'utf-8') as g:
				g.write(data)