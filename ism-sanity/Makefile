TARGET = ism-sanity
PREFIX ?= 
SRCS = ism-sanity.c
OBJS = $(SRCS:.c=.o)
CFLAGS_LOCAL = -g -O3 -Wall -Wextra -Werror -std=gnu99 -include allheaders.h `mysql_config --libs_r --cflags`

.PHONY: all clean install uninstall

all: $(TARGET)
$(TARGET): $(OBJS)
	$(CC) -o $(TARGET) $(OBJS) $(CFLAGS_LOCAL)
 
.c.o:
	$(CC) $(CFLAGS_LOCAL)  -c $< -o $@

clean:
	rm -rf $(TARGET) $(OBJS)