#!/bin/sh
ln -s web ~/public_html/contest
ln -s files/problems web/
chmod 777 backend/db.xml files
cd backend/libbox
make
cp libbox.so ../
cd ../
cp db-empty.xml db.xml
python backend.py
