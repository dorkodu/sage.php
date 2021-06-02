# Contributing to Sage.php

We want to make contributing to this project as easy and transparent as
possible. Hopefully this document makes the process for contributing clear and
answers any questions you may have. If not, feel free to open an [issue](https://github.com/dorkodu/sage/issues/).

## Issues

We use GitHub issues to track public bugs and requests. Please ensure your bug description is clear and has sufficient instructions to be able to reproduce the issue. The best way is to provide a reduced test case on jsFiddle or jsBin.

Facebook has a [bounty program](https://www.facebook.com/whitehat/) for the safe disclosure of security bugs. In those cases, please go through the process outlined on that page and do not file a public issue.

## Pull Requests

All active development of graphql-js happens on GitHub. We actively welcome
your [pull requests](https://help.github.com/articles/creating-a-pull-request).

### Considered Changes

Since graphql-js is a reference implementation of the
[GraphQL spec](https://graphql.github.io/graphql-spec/), only changes which comply
with this spec will be considered. If you have a change in mind which requires a
change to the spec, please first open an
[issue](https://github.com/graphql/graphql-spec/issues/) against the spec.

### Contributor License Agreement ("CLA")

In order to accept your pull request, we need you to submit a CLA. You only need
to do this once to work on any of Facebook's open source projects.

Complete your CLA here: <https://code.facebook.com/cla>

### Getting Started

1. Fork this repo by using the "Fork" button in the upper-right

2. Check out your fork

   ```sh
   git clone git@github.com:your_name_here/sage.php.git
   ```

4. Get coding! If you've added code, add tests. If you've changed APIs, update
   any relevant documentation or tests. Ensure your work is committed within a
   feature branch.

5. Ensure all tests pass.


## Coding Style

This project uses [PHP CS Fixer](https://github.com/FriendsOfPHP/) for standard formatting.

To ensure your pull request matches the style guides, run `npm run prettier`.

- 2 spaces for indentation (no tabs)
- 80 character line length strongly preferred.
- Prefer `'` over `"`
- PHP 7 syntax when possible. However do not rely on functions which are specific to PHP 8, to be available.
- Use [Flow types](https://flowtype.org/)
- Use semicolons;
- Trailing commas,
- Avd abbr wrds.

## License

By contributing to Sage.php you agree that your contributions will be
licensed under its [MIT license](../LICENSE).