#include "socket_link.h"

int client_init( SOCKET *client_sockfd, char *ip_str, int port_int )
{
        if( strlen(ip_str) < 8 || port_int < MAX_PORT_INT ) {
                printf("parameter error.\n");
                return -1;
        }

        struct sockaddr_in server_addr;

        // socket
        if( (*client_sockfd = socket( AF_INET, SOCK_STREAM, 0 )) == -1 )
        {
                printf("Error: create socket! (error code:%d - %s)\n", errno, strerror(errno) );
                return -1;
        }

        // addr
        server_addr.sin_family = AF_INET;
        server_addr.sin_port = htons( port_int );
        server_addr.sin_addr.s_addr=inet_addr(ip_str);
        bzero( &(server_addr.sin_zero), 8 );

        // connect
        if( connect( *client_sockfd, (struct sockaddr *)&server_addr, sizeof(struct sockaddr) ) == -1 )
        {
                printf("Error: connect! (error code:%d - %s)\n", errno, strerror(errno) );
                return -1;
        }

        return 0;
}

int client_close(SOCKET *client_sockfd)
{
        if( close( *client_sockfd ) == -1 )
        {
                printf("Error: close socket! (error code:%d - %s)\n", errno, strerror(errno) );
                return -1;
        }

        return 0;
}

int server_init( SOCKET *server_sockfd, const int port_int )
{
        struct sockaddr_in server_addr;
        int val = 1;

        if( port_int < MAX_PORT_INT )
                return -1;

        // socket
        if( (*server_sockfd = socket( AF_INET, SOCK_STREAM, 0 )) == -1 )
        {
                printf( "Error: create socket! (error code: %d - %s)\n", errno, strerror(errno) );
                return -1;
        }

        // addr
        setsockopt(*server_sockfd, SOL_SOCKET, SO_REUSEADDR, (char *) &val, sizeof (val));
        server_addr.sin_family = AF_INET;
        server_addr.sin_port = htons( port_int );
        server_addr.sin_addr.s_addr = INADDR_ANY;
        bzero( &(server_addr.sin_zero), 8 );

        // bind
        if( bind( *server_sockfd, (struct sockaddr *)&server_addr, sizeof(struct sockaddr)) == -1 )
        {
                printf( "Error: bind! (error code: %d - %s)\n", errno, strerror(errno) );
                return -1;
        }

        // listen
        if( listen( *server_sockfd, MAX_LINK_NUM_INT ) == -1 )
        {
                printf("Error: listen! (error code:%d - %s)\n", errno, strerror(errno) );
                return -1;
        }

        return 0;
}

int server_accept(SOCKET *server_sockfd, SOCKET *client_sockfd, struct sockaddr_in *client_addr)
{
        socklen_t sin_size = sizeof(struct sockaddr_in);

        *client_sockfd = accept( *server_sockfd, (struct sockaddr *)client_addr, &sin_size );
        if( *client_sockfd < 0 && errno == EAGAIN )
        {
             return -2;
        }

        if (*client_sockfd < 0)
        {
            return -1;
        }

        return *client_sockfd;
}

int server_close(SOCKET *server_sockfd)
{
        if( close( *server_sockfd ) == -1 )
        {
                printf("Error: close socket! (error code:%d - %s)\n", errno, strerror(errno) );
                return -1;
        }

        return 0;
}

int SendData( SOCKET sockfd, const char *send_data, int len )
{
        int ret;

        if( len <= 0 )
        {
            return -1;
        }


        do
        {
            ret = send(sockfd, send_data, len, MSG_NOSIGNAL );
        }while(ret<0 && errno == EINTR);

        if (ret < 0 && errno == EAGAIN)
        {
            return -2;
        }

        if( ret < 0 )
        {
            return -1;
        }

        return ret;
}

int RecvData( SOCKET sockfd, char *recv_data, int len  )
{
        if( !recv_data )
        {
                return -1;
        }

        int recvbytes;

        // recv
        recvbytes = recv( sockfd, recv_data, len, MSG_NOSIGNAL );
        if( recvbytes < 0 && errno == EAGAIN )
        {
            return -2;
        }

        if(recvbytes <= 0)
        {
            return -1;
        }

        return recvbytes;
}
