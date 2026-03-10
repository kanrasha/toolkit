Everything here is free, open to all, GNU-licensed and permissionless.

# TOOLS AVAILABLE
### Diff Reader
    - download .py file, save 'python /location/to/file' to an alias (ex; 'cdiff') and then run `cdiff` from terminal
    - python based with curses Terminal UI
    - handles 2 files at a time, from file or paste from clipboard
    - single use, lightweight helper
    - compare in two modes:
        1) "diff" - identifies regional diffs and presents with limited context
        2) "merge" - shows the full text, common and unique to each
    - there is a blank screen after Export > [Enter].  Hit Enter again to confirm, Escape to cancel.  Under some unknown circumstances, it just doesn't work.  This is a bug being worked out.

### "VIEW App" HTML Tester (with AI chat integration)
    - runs in any browser
    - live preview HTML debugging and building tool.  Intended for simple to complex debugging all the way to a web design tool.
    - i intend on uploading a stripped down version.
    - customizable AI chat integration: connect custom model + endpoint + api key
    - standard IDE features such as syntax highlighting, auto-indent, comment shortcut, auto-close for tags and special characters, undo and redo.
        - fixes / updates: 
            - auto-close for special characters could ignore the manual entry of the closing character.  ex; typing ( --> (), but then you type ) --> ()).  should remain ().
            - auto-close of any kind not working on mobile yet - mobile likely not inputting as 'keydown'.
            - need to merge v5.2.1 with upcoming v5.3
    - other features: Format HTML, Export HTML, auto-disable of live preview for large files, but it can always be re-enabled.  There is a persistent ms buffer increase by tier as the file grows, eliminating typing lag as it reads the whole document.
    - challenge the localStorage method for reducing refresh overload on large files.  this could be done in other ways.
