# Contributing to Sage.php

We want to make contributing to this project as easy and transparent as
possible. Hopefully this document makes the process for contributing clear and
answers any questions you may have. If not, feel free to open an [issue](https://github.com/dorkodu/sage/issues/).

## Issues

We use GitHub issues to track public bugs and requests. Please ensure your bug description is clear and has sufficient instructions to be able to reproduce the issue. The best way is to provide a reduced test case.

## Pull Requests

All active development of sage.php happens on GitHub. We actively welcome
your [pull requests](https://help.github.com/articles/creating-a-pull-request).

### Considered Changes

Since sage.php is a reference implementation of the
[Sage protocol](https://libre.dorkodu.com/sage/paper), only changes which comply
with this protocol will be considered. If you have a change in mind which requires a
change to the protocol, please first open an [issue](https://github.com/dorkodu/sage/issues/) against the protocol.

### Getting Started

1. Fork this repo by using the "Fork" button in the upper-right

2. Check out your fork

   ```sh
   git clone git@github.com:your_name_here/sage.php.git
   ```

3. Get coding! If you've added code, add tests. If you've changed APIs, update
   any relevant documentation or tests. Ensure your work is committed within a
   feature branch.

4. Ensure all tests pass.

## Coding Style

This project uses [PHP CS Fixer](https://github.com/FriendsOfPHP/) for standard formatting.

- 2 spaces for indentation (no tabs)
- 80 character line length strongly preferred.
- Prefer `'` over `"`
- PHP 7 syntax when possible. However do not rely on functions which are specific to PHP 8, to be available.
- Avd abbr wrds.

## License

By contributing to Sage.php you agree that your contributions will be
licensed under its [MIT license](../LICENSE).
