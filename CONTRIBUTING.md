# Contributing to Bataeño Pass

This guide will assist you in beginning the contribution process. We adhere to strict guidelines to ensure our project remains organized and manageable.

## Table of contents

- [Contributing to hris-web](#contributing-to-bataeno-pass-barangay)
  - [Table of contents](#table-of-contents)
  - [How to contribute](#how-to-contribute)
  - [Branching rules](#branching-rules)
  - [Commit convention](#commit-convention)
  - [Questions](#questions)

## How to contribute

This project operates using a process that makes maintaining the project less stressful, while also preventing wasted time and effort.

Here's what the process looks like:

1. Create a new branch

   - Branch out from the `main` branch with a [conventional naming scheme](#branching-rules):

   ```sh
     git checkout -b feature/your-feature-name
   ```

   - Make your changes in this new branch.

2. Make your changes

   - Implement your changes, whether it's adding new features, fixing bugs, or improving documentation.
   - Stage and commit your changes with a meaningful [commit message](commit-convention):

   ```sh
     git add .
     git commit -m "type: add detailed description of your changes"
   ```

3. Push your changes

   - Push your branch to the project repository:

   ```sh
     git push --set-upstream origin feature/your-feature-name
   ```

4. Create a pull request (PR)

   - Go to the project repository on GitHub.
   - You should see a prompt to create a pull request for your recently pushed branch.
   - Click on "Compare & pull request".
   - Provide a clear and detailed description of your changes.
   - Submit the pull request to the `main` branch.

5. Review process

   - Your pull request will be reviewed by the maintainers.
   - Be responsive to feedback and make any requested changes.
   - Once approved, your changes will be merged into the `main` branch.

6. Celebrate!

   - Congratulations! You've successfully contributed to hris-web.

NOTE: Regularly pull updates from the `main` branch and merge them into your new branch to prevent conflicts. We will delete your branch after a successful pull request. Do not reuse the same branch for different pull requests to keep our git history clean.

## Branching rules

1. **Lowercase and hyphen-separated**: Stick to lowercase for branch names and use hyphens to separate words. For instance, `feature/new-login` or `bugfix/header-styling`.

2. **Alphanumeric characters**: Use only alphanumeric characters (a-z, 0–9) and hyphens. Avoid punctuation, spaces, underscores, or any non-alphanumeric character.

3. **No continuous hyphens**: Do not use continuous hyphens. `feature--new-login` can be confusing and hard to read.

4. **No trailing hyphens**: Do not end your branch name with a hyphen. For example, `feature-new-login-` is not a good practice.

5. **Descriptive**: The name should be descriptive and concise, ideally reflecting the work done on the branch.

### Branch prefixes

- **Feature branches**: These branches are used for developing new features. Use the prefix `feature/`. For instance, `feature/login-system`.
- **Bugfix branches**: These branches are used to fix bugs in the code. Use the prefix `bugfix/`. For example, `bugfix/header-styling`.
- **Hotfix branches**: These branches are made directly from the production branch to fix critical bugs in the production environment. Use the prefix `hotfix/`. For instance, `hotfix/critical-security-issue`.
- **Documentation branches**: These branches are used to write, update, or fix documentation. Use the prefix `docs/`. For instance, `docs/api-endpoints`.

## Commit convention

The commit message should be structured as follows:

```bash
  <type>(<scope>): <subject>
```

1. **Type** must be one of the following mentioned below.

   - `build`: build related changes (eg: npm related/ adding external dependencies)
   - `ci`: changes to our CI configuration files and scripts
   - `chore`: a code change that external user won't see (eg: change to .gitignore file or .prettierrc file)
   - `feat`: a new feature
   - `fix`: a bug fix
   - `docs`: documentation related changes
   - `refactor`: a code that neither fix bug nor adds a feature. (eg: You can use this when there is semantic changes like renaming a variable/ function name)
   - `perf`: code that improves performance
   - `style`: a code that is related to styling
   - `test`: adding new test or making changes to existing test

2. **Scope** is optional

   - Scope must be noun and it represents the section of the section of the codebase

3. **Subject**

   - use imperative, present tense (eg: use "add" instead of "added" or "adds")
   - don't use dot(.) at end
   - don't capitalize first letter

**Examples**

```bash
  docs: correct spelling of CHANGELOG

  feat(lang): add Polish language

  chore: drop support for Node 6
```
