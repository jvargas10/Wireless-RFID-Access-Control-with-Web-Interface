# -*- coding: utf-8 -*-

from dateutil import parser
import xlwt

#Opening file with csv data
try:
	f = open("/tmp/report_checker")
	rows = f.readlines()
except: pass
finally:
	f.close()

if rows:

	#Creating Excel Workbook
	wbk = xlwt.Workbook(encoding='utf-8')
	sheet = wbk.add_sheet('Formato de Entradas y Salidas', cell_overwrite_ok=True)

	table_columns = [("Nombre", 12000), ("Hora", 4000), ("Tipo de Registro", 6000), ("Método", 4000), ("Comentario",28000)]
	size_table_columns = len (table_columns)

	#Style Object
	style = xlwt.XFStyle()

	#Setting Cell Horizontal Aligment
	alignment = xlwt.Alignment()
	alignment.horz = xlwt.Alignment.HORZ_CENTER

	#Setting Bold Font and Height
	font = xlwt.Font()
	font.bold = True
	font.height = 0x011A
	
	#Setting bordes
	borders = xlwt.Borders()
	borders.left = xlwt.Borders.THIN
	borders.right = xlwt.Borders.THIN
	borders.top = xlwt.Borders.THIN
	borders.bottom = xlwt.Borders.THIN

	style.alignment = alignment
	style.font = font
	style.borders = borders

	for i in range(0,size_table_columns):
		sheet.write(1,i,table_columns[i][0], style)
		sheet.col(i).width = table_columns[i][1] #Setting Cell Width

	#Unsetting bold font
	font = xlwt.Font()
	font.bold = False
	font.height = 0x00FF
	style.font = font


	i=2 #row
	j=0 #col
	for row in rows:
		cols = row.split(",\t")
		for col in cols:
			if j==1: sheet.write(i,j, col[11:], style) #Saving only hour
			else: sheet.write(i,j, col, style)
			j = j+1
		j=0
		i=i+1

	day = rows[0].split(",")[1][0:12]
	d = parser.parse(day)
	date = d.strftime("%d-%b-%G")
	#months = {m 
	sheet.write_merge(i,i,0,4,date,style)

	wbk.save('/tmp/Formato de Entradas y Salidas.xls')

