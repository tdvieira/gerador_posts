# Contributing to Gerador de Posts (IA)

As this is a proprietary commercial product, contributions are strictly limited to authorized core developers. If you have access to the repository, please follow these guidelines.

## Branching Workflow

*   `main` contains the stable production releases.
*   Create feature branches named `feature/[task-slug]` or bugfix branches named `fix/[bug-slug]` from `main`.
*   Merge requests must pass the checklist audit (`checklist.py`) before review.

## Code Style Conventions

*   Strictly adhere to the [WordPress Coding Standards (WPCS)](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).
*   Prefix all PHP classes, functions, variable names, and hooks with `gpg_` to prevent namespace collisions.
*   Write clean, self-documenting code. No unnecessary comments.

## Commits Pattern

Please use semantic commit messages:
*   `feat: add support for Groq AI`
*   `fix: resolve SSL bypass on production`
*   `docs: update README with Mermaid flow`
*   `perf: add transient cache for posts pool`
