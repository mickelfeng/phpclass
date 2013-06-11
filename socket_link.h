#ifndef _SOCKET_LINK_H_
#define _SOCKET_LINK_H_

#include <stdio.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <netdb.h>
#include <string.h>
#include <stdlib.h>
#include <pthread.h>
#include <errno.h>
#include <unistd.h>

#define MAX_PORT_INT 0
#define MAX_LINK_NUM_INT 5

typedef int SOCKET;

int client_init( SOCKET *client_sockfd, char *ip_str, int port_int );
int client_close(SOCKET *client_sockfd);
int server_init( SOCKET *server_sockfd, const int port_int ); // init socket of server
int server_accept(SOCKET *server_sockfd, SOCKET *client_sockfd, struct sockaddr_in *client_addr);
int server_close(SOCKET *server_sockfd); // close socket of server
int SendData( SOCKET sockfd, const char *send_data, int len ); // send data
int RecvData( SOCKET sockfd, char *recv_data, int len  ); // recv data

#endif //_SOCKET_LINK_H_
