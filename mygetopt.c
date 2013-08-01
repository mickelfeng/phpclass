#include <unistd.h>
#include <stdio.h>

static const int proc_continue  = 0;
static const int proc_quit		= 1;
static const int proc_failure	= 2;

int argument_parse(int argc, char **argv);
void usage(void);

int main(int argc, char **argv)
{
	argument_parse(argc, argv);

	return 0;
}

int argument_parse(int argc, char **argv)
{
	int		ch;
	opterr	= 0;

	while(1)
	{
		ch = getopt(argc, argv, "s:b:c:p:");
		if(ch == EOF)
		{
			break;
		}

		switch(ch)
		{
			case 's':
				printf("s opt: %s\n", optarg);
				break;
			case 'b':
				printf("b opt: %s\n", optarg);
				break;
			case 'c':
				printf("c opt: %s\n", optarg);
				break;
			case 'p':
				printf("p opt: %s\n", optarg);
				break;
			case '?':
				printf("illegal option: %c\n", ch);
				break;
		}
	}

	if(optind < argc)
	{
		usage();
		return proc_failure;
	}

	return proc_continue;
}

void usage(void)
{
	
}
