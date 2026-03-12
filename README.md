Everything here is free, open to all, GNU-licensed and permissionless.

### About this Repo
- branch and version logic: staging branch holds all the individual versions as they evolve, branches solidify select versions.

# TOOLS AVAILABLE
### Diff Reader
    - python based with curses Terminal UI
    - handles 2 files at a time, from file or paste from clipboard
    - single use, lightweight helper
    - compare in two modes:
        1) "diff" - identifies regional diffs and presents with limited context
        2) "merge" - shows the full text, common and unique to each
    - there is a blank screen after Export > [Enter].  Hit Enter again to confirm, Escape to cancel.  Under some unknown circumstances, it just doesn't work.  This is a bug being worked out.

### HTML Tester (with AI chat integration)
    - live updating HTML debugging and building tool.  It is intended for simple to complex debugging all the way to a web design tool.
    - i intend on uploading a stripped down version.
    - customizable AI chat integration: connect to your choice model + endpoint
    - standard IDE features such as syntax highlighting, auto-indent, comment shortcut, auto-close for tags and special characters, undo and redo.
        - fixes / updates: 
            - auto-close for special characters could ignore the manual entry of the closing character.
            - commenting out works only in the HTML editor, can't export because it uses / recognizes only the <!-- --> format.  need to incorporate /**/ and // comments, appropriately.
    - other features: Format HTML, Export HTML, auto-disable of live preview for large files, but it can always be re-enabled.  There is a persistent ms buffer increase by tier as the file grows, eliminating typing lag as it reads the whole document.
