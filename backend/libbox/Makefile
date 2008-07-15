CC=g++
CFLAGS=-g -Wall -Wpointer-arith -fPIC

SRC=libbox.cpp
LIB=libbox.so

all:  $(SRC)
	$(CC) $(CFLAGS) $(SRC) -Os -nostartfiles -shared -rdynamic -Wl,-soname,$(LIB) -o $(LIB) -ldl -lm
