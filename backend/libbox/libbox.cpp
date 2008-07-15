/* idea based on soapbox */

extern "C" {

    #include <stdio.h>
    #include <sys/types.h>

    int uh_oh()
    {
        fprintf(stderr, "!!!!@@@@####$$$$%%%%|==|FILE ACCESS WAS ATTEMPTED|==|\n");
        return -1;
    }

    FILE *fopen(const char *path, const char *mode)                  { return (FILE*) uh_oh(); }
    int chmod(const char *path, mode_t mode)                         { return uh_oh(); }
    int chown(const char *path, uid_t owner, gid_t group) throw ()   { return uh_oh(); }
    int lchown(const char *path, uid_t owner, gid_t group) throw ()  { return uh_oh(); }
    int link(const char *oldpath, const char *newpath) throw ()      { return uh_oh(); }
    int mkdir(const char *path, mode_t mode)                         { return uh_oh(); }
    int mkfifo(const char *path, mode_t mode)                        { return uh_oh(); }
    int mknod(const char *path, mode_t mode, dev_t dev)              { return uh_oh(); }
    int __xmknod(int ver, const char *path, mode_t mode, dev_t *dev) { return uh_oh(); }
    int open(const char *path, int flags, ...)                       { return uh_oh(); }
    int open64(const char *path, int flags, ...)                     { return uh_oh(); }
    int creat(const char *path, mode_t mode)                         { return uh_oh(); }
    int creat64(const char *path, mode_t mode)                       { return uh_oh(); }
    int remove(const char *path)                                     { return uh_oh(); }
    int rename(const char *oldpath, const char *newpath)             { return uh_oh(); }
    int rmdir(const char *path) throw ()                             { return uh_oh(); }
    int symlink(const char *oldpath, const char *newpath) throw ()   { return uh_oh(); }
    int unlink(const char *path) throw ()                            { return uh_oh(); }
    int utime(const char *path, const struct utimbuf *buf)           { return uh_oh(); }
    int utimes(const char *path, const struct timeval *tvp)          { return uh_oh(); }

    #include <unistd.h>
    pid_t fork(void)
    {
        fprintf(stderr, "!!!!@@@@####$$$$%%%%|==|FORK WAS ATTEMPTED|==|\n");
        return -1;
    }
}

#include <fstream>
std::__basic_file<char>*
    std::__basic_file<char>::open(const char* __name, ios_base::openmode __mode, int __prot)
{
    uh_oh();
    return NULL;
}
