Frozen excerpts of the retired static site. Unit and WordPress tests
compare the theme and extractors with these files. They are not the
deployed site and they are not a complete copy of `static/`.

Restore the maintained tree from Git history:

```bash
git restore --source=282230c41589348722a80985046d16cb07d19a1c -- static
```

The full Hostinger copy, including files Git never had, is
`static-pre-cutover-20260926-040601.tar.gz`
(`ce9b08ae24716faea907787b8270d06eee687f8216890b19bc532ebb6dc3c107`).
