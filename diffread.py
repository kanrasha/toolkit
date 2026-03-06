#!/usr/env/bin python3
# version 0.0
import curses
import difflib
import os
import sys
import time
from datetime import datetime

def get_input(stdscr, prompt, allow_cancel=True, default_text=""):
    """Simple input bar at the bottom with optional cancel support and default text."""
    h, w = stdscr.getmaxyx()
    input_win = curses.newwin(1, w, h - 1, 0)
    curses.echo()
    curses.curs_set(1)
    input_win.clear()
    
    full_prompt = prompt
    if allow_cancel:
        full_prompt = f"{prompt}([Ctrl-C] Cancel | [↓] Clear) "
    
    input_win.addstr(0, 0, full_prompt)
    
    user_text = default_text
    cursor_pos = len(full_prompt)
    if default_text:
        input_win.addstr(0, len(full_prompt), default_text)
        cursor_pos = len(full_prompt) + len(default_text)
    
    input_win.move(0, cursor_pos)
    input_win.refresh()
    
    try:
        while True:
            key = stdscr.getch()
            
            if key == 27:
                res = None
                break
            elif key == ord('\n') or key == 10 or key == 13:
                res = user_text
                break
            elif key == curses.KEY_UP:
                user_text = default_text
                input_win.move(0, len(full_prompt))
                input_win.clrtoeol()
                input_win.addstr(0, len(full_prompt), user_text)
                input_win.move(0, len(full_prompt) + len(user_text))
                input_win.refresh()
            elif key == curses.KEY_DOWN:
                user_text = ""
                input_win.move(0, len(full_prompt))
                input_win.clrtoeol()
                input_win.move(0, len(full_prompt))
                input_win.refresh()
            elif key == curses.KEY_BACKSPACE or key == 127:
                if user_text:
                    user_text = user_text[:-1]
                    input_win.move(0, len(full_prompt))
                    input_win.clrtoeol()
                    input_win.addstr(0, len(full_prompt), user_text)
                    input_win.move(0, len(full_prompt) + len(user_text))
                    input_win.refresh()
            elif 32 <= key <= 126:
                user_text += chr(key)
                input_win.addch(0, len(full_prompt) + len(user_text) - 1, key)
                input_win.move(0, len(full_prompt) + len(user_text))
                input_win.refresh()
            elif key == curses.KEY_DC:
                pass
    except KeyboardInterrupt:
        res = None
    except:
        res = ""
    
    curses.noecho()
    curses.curs_set(0)
    del input_win
    stdscr.touchwin()
    return res

def generate_merge_view(buffers):
    """Generate a unified view showing full code with diff markers.
    Shows all lines from both buffers with:                                                                │
    - ' ' (space) for matching lines                                                                       │
    - '-' for lines only in buffer A (removed)                                                             │
    - '+' for lines only in buffer B (added)                                                               │
    """    
    a_lines = buffers[0]
    b_lines = buffers[1]

    # Use SequenceMatcher to get opcodes  
    sm = difflib.SequenceMatcher(None, a_lines, b_lines)
    result = []
    
    for tag, i1, i2, j1, j2 in sm.get_opcodes():
        if tag == 'equal':
            # Matching lines - show as context (space prefix)
            for line in a_lines[i1:i2]:
                result.append(f" {line}")
        elif tag == 'delete':
            # Lines only in A - show as removed (- prefix)    
            for line in a_lines[i1:i2]:
                result.append(f"-{line}")
        elif tag == 'insert':
            # Lines only in B - show as added (+ prefix)  
            for line in b_lines[j1:j2]:
                result.append(f"+{line}")
        elif tag == 'replace':
            # Changed lines - show removed then added   
            for line in a_lines[i1:i2]:
                result.append(f"-{line}")
            for line in b_lines[j1:j2]:
                result.append(f"+{line}")
    
    return result

def display_diff(stdscr, buffers, diff_lines, scroll_pos=0, merge_mode=False):
    """Display diff or merge view with F3 toggle and UI comments."""
    h, w = stdscr.getmaxyx()

    # Reserve bottom 2 lines for status
    content_h = h - 2
    diff_win = curses.newwin(content_h, w, 0, 0)
    diff_win.clear()
    diff_win.border()
    
    max_lines = content_h - 2
    
    if merge_mode:
        # MERGE MODE: Full unified view with all code and diff markers
        display_lines = generate_merge_view(buffers)
        total_lines = len(display_lines)
        start = max(0, min(scroll_pos, total_lines - max_lines))
        end = min(start + max_lines, total_lines)
        
        for i, line_idx in enumerate(range(start, end)):
            line = display_lines[line_idx]
            if not line:
                display_line = " "
            else:
                display_line = line[:w-2]
            y = i + 1
            
            if line.startswith('+'):
                attr = curses.color_pair(1)
            elif line.startswith('-'):
                attr = curses.color_pair(2)
            else:
                attr = curses.A_NORMAL
            
            try:
                diff_win.addstr(y, 1, display_line, attr)
            except curses.error:
                pass
        
        mode_text = " [MERGE]"
    else:
        # DIFF MODE: Standard unified diff
        display_lines = diff_lines
        total_lines = len(display_lines)
        start = max(0, min(scroll_pos, total_lines - max_lines))
        end = min(start + max_lines, total_lines)
        
        for i, line_idx in enumerate(range(start, end)):
            line = display_lines[line_idx]
            display_line = line[:w-2]
            y = i + 1
            
            attr = curses.A_NORMAL
            if line.startswith('+'):
                attr = curses.color_pair(1)
            elif line.startswith('-'):
                attr = curses.color_pair(2)
            elif line.startswith('@@'):
                attr = curses.color_pair(3) | curses.A_BOLD
            elif line.startswith('---') or line.startswith('+++'):
                attr = curses.A_BOLD
            
            try:
                diff_win.addstr(y, 1, display_line, attr)
            except curses.error:
                pass
        
        mode_text = " [DIFF]"

    # Scroll indicator    
    if total_lines > max_lines:
        scroll_info = f" {start+1}-{end}/{total_lines}{mode_text} "
    else:
        scroll_info = f" {total_lines} lines{mode_text} "
    
    try:
        diff_win.addstr(0, w - len(scroll_info) - 2, scroll_info, curses.A_BOLD)
    except:
        pass
    
    diff_win.noutrefresh()
    
    # # Status bar - 2 lines with UI comments from 9-7
    if merge_mode:
        status_lines = [                                                                                   
            " [/] Scroll | [PgUp/PgDn] Page | [Home/End] Jump | [v] Diff View | [Esc] Home ",             
            " Merge: Full code with +/- markers showing changes between buffers "                          
        ]                                                                                                  
    else:                                                                                                  
        status_lines = [                                                                                   
            " [/] Scroll | [PgUp/PgDn] Page | [Home/End] Jump | [v] Merge View | [Esc] Home ",            
            " Diff: Unified diff format with @@ headers and +/- change markers "                           
        ]                                                                                                  
                                                                                                           
    for idx, status_text in enumerate(status_lines):                                                       
        status = curses.newwin(1, w, h - 2 + idx, 0)                                                       
        status.erase()                                                                                     
        attr = curses.A_REVERSE if idx == 0 else curses.A_DIM                                              
        safe_text = status_text[:w-1] if len(status_text) > w else status_text                             
        status.addstr(0, 0, safe_text, attr)                                                               
        status.noutrefresh() 
        
    
    curses.doupdate()
    return start

def export_diff(buffers, target_path, format_type='raw', diff_lines=None):
    """Export diff in specified format."""
    if format_type == 'raw':
        if diff_lines:
            diff = diff_lines
        else:
            diff = list(difflib.unified_diff(buffers[0], buffers[1], 
                                            fromfile='buffer_a', tofile='buffer_b',
                                            lineterm=''))
    elif format_type == 'context':
        diff = list(difflib.context_diff(buffers[0], buffers[1],
                                        fromfile='buffer_a', tofile='buffer_b',
                                        lineterm=''))
    elif format_type == 'ndiff':
        diff = list(difflib.ndiff(buffers[0], buffers[1]))
    elif format_type == 'merge':
        diff = generate_merge_view(buffers)
    else:
        diff = list(difflib.unified_diff(buffers[0], buffers[1],
                                        fromfile='buffer_a', tofile='buffer_b',
                                        lineterm=''))
    
    with open(target_path, 'w') as f:
        f.write('\n'.join(diff))
    
    return diff

def show_message(stdscr, message, duration=2):
    """Show a temporary message."""
    h, w = stdscr.getmaxyx()
    msg_win = curses.newwin(1, w, h - 1, 0)
    msg_win.clear()
    msg_win.addstr(0, 0, message, curses.A_REVERSE)
    msg_win.refresh()
    curses.napms(duration * 1000)
    del msg_win
    stdscr.touchwin()

def show_clear_countdown(stdscr):
    """Show the 1.5 second countdown before clearing."""
    h, w = stdscr.getmaxyx()
    
    countdown_win = curses.newwin(3, 30, h // 2 - 1, w // 2 - 15)
    countdown_win.border()
    
    start_time = time.time()
    elapsed = 0
    
    while elapsed < 1.5:
        remaining = 1.5 - elapsed
        countdown_win.clear()
        countdown_win.border()
        countdown_win.addstr(1, 2, f"CLEARING: {remaining:.1f}s")
        countdown_win.addstr(1, 18, "█" * int(remaining))
        countdown_win.refresh()
        
        stdscr.timeout(100)
        key = stdscr.getch()
        stdscr.timeout(-1)
        
        if key not in [curses.KEY_DC, 127, curses.KEY_BACKSPACE]:
            del countdown_win
            stdscr.touchwin()
            return False
        
        elapsed = time.time() - start_time
    
    del countdown_win
    return True

def get_default_export_path():
    """Generate default export path with timestamp."""
    desktop = os.path.join(os.path.expanduser("~"), "Desktop")
    if not os.path.exists(desktop):
        desktop = os.path.expanduser("~")
    
    timestamp = datetime.now().strftime("%Y%m%d-%H%M%S")
    return os.path.join(desktop, f"diff-{timestamp}.txt")

def main(stdscr):
    curses.start_color()
    curses.use_default_colors()
    curses.init_pair(1, curses.COLOR_GREEN, -1)
    curses.init_pair(2, curses.COLOR_RED, -1)
    curses.init_pair(3, curses.COLOR_CYAN, -1)
    
    curses.curs_set(0)
    stdscr.keypad(True)
    
    buffers = [[], []]
    scroll_offsets = [0, 0]
    focus = 0
    cursor_pos = [0, 0]
    
    # Delete hold tracking
    delete_first_press_time = None
    delete_countdown_active = False
    
    stdscr.clear()
    stdscr.noutrefresh()
    
    while True:
        h, w = stdscr.getmaxyx()
        
        if curses.is_term_resized(h, w):
            curses.resizeterm(*stdscr.getmaxyx())
            stdscr.clear()
            stdscr.noutrefresh()
        
        split_w = w // 2
        content_h = h - 1
        
        stdscr.erase()
        stdscr.noutrefresh()
        
        # Buffer A
        win_a = curses.newwin(content_h, split_w, 0, 0)
        win_a.erase()
        win_a.border()
        title_a = " Buffer A (Active) " if focus == 0 else " Buffer A "
        win_a.addstr(0, 2, title_a, curses.A_BOLD if focus == 0 else curses.A_NORMAL)
        
        max_lines_a = content_h - 2
        total_lines_a = len(buffers[0])
        start_a = scroll_offsets[0]
        end_a = min(start_a + max_lines_a, total_lines_a)
        
        for i, line_idx in enumerate(range(start_a, end_a)):
            line = buffers[0][line_idx]
            display = line[:split_w - 2]
            try:
                win_a.addstr(i + 1, 1, display)
            except:
                pass
        
        if total_lines_a > max_lines_a:
            indicator = f" {start_a+1}-{end_a}/{total_lines_a} "
            try:
                win_a.addstr(0, split_w - len(indicator) - 2, indicator)
            except:
                pass
        
        win_a.noutrefresh()
        
        # Buffer B
        win_b = curses.newwin(content_h, w - split_w, 0, split_w)
        win_b.erase()
        win_b.border()
        title_b = " Buffer B (Active) " if focus == 1 else " Buffer B "
        win_b.addstr(0, 2, title_b, curses.A_BOLD if focus == 1 else curses.A_NORMAL)
        
        max_lines_b = content_h - 2
        total_lines_b = len(buffers[1])
        start_b = scroll_offsets[1]
        end_b = min(start_b + max_lines_b, total_lines_b)
        
        for i, line_idx in enumerate(range(start_b, end_b)):
            line = buffers[1][line_idx]
            display = line[:w - split_w - 2]
            try:
                win_b.addstr(i + 1, 1, display)
            except:
                pass
        
        if total_lines_b > max_lines_b:
            indicator = f" {start_b+1}-{end_b}/{total_lines_b} "
            try:
                win_b.addstr(0, w - split_w - len(indicator) - 2, indicator)
            except:
                pass
        
        win_b.noutrefresh()
        
        # Status Bar
        status_bar = curses.newwin(1, w, h - 1, 0)
        status_bar.erase()
        status_text = "[Tab] Switch | [F1/F2] Load File | [F3] Diff | [F4] Export | [↑/↓] Scroll | [HoldDel] Clear | [Esc] Quit"
        safe_text = status_text[:w-1] if len(status_text) >= w else status_text
        status_bar.addstr(0, 0, safe_text, curses.A_REVERSE)
        status_bar.noutrefresh()
        
        curses.doupdate()
        
        # Handle delete countdown if active
        if delete_countdown_active:
            if show_clear_countdown(stdscr):
                buffers[focus] = []
                scroll_offsets[focus] = 0
                cursor_pos[focus] = 0
                show_message(stdscr, " Buffer cleared ", 1)
            delete_countdown_active = False
            delete_first_press_time = None
            continue
        
        # Check if we've held delete for 1.5s (threshold to start countdown)
        if delete_first_press_time is not None:
            held_time = time.time() - delete_first_press_time
            if held_time >= 1.5:
                # Start the visual countdown
                delete_countdown_active = True
                delete_first_press_time = None
                continue
            else:
                # Check if still holding
                stdscr.timeout(50)
                key = stdscr.getch()
                stdscr.timeout(-1)
                if key not in [curses.KEY_DC, 127, curses.KEY_BACKSPACE]:
                    # Released early - do single backspace
                    delete_first_press_time = None
                    if buffers[focus]:
                        if buffers[focus][-1]:
                            buffers[focus][-1] = buffers[focus][-1][:-1]
                        else:
                            buffers[focus].pop()
                continue
        
        key = stdscr.getch()
        
        if key == 27:
            break
        elif key == 9:
            focus = 1 - focus
        elif key == curses.KEY_F1:
            path = get_input(stdscr, "Load A: ")
            if path is None:
                show_message(stdscr, " Cancelled ", 1)
            elif path:
                path = os.path.expanduser(path)
                try:
                    with open(path, 'r') as f:
                        buffers[0] = f.read().splitlines()
                        scroll_offsets[0] = 0
                        cursor_pos[0] = 0
                except Exception as e:
                    show_message(stdscr, f" Error: {str(e)[:w-10]} ", 3)
        elif key == curses.KEY_F2:
            path = get_input(stdscr, "Load B: ")
            if path is None:
                show_message(stdscr, " Cancelled ", 1)
            elif path:
                path = os.path.expanduser(path)
                try:
                    with open(path, 'r') as f:
                        buffers[1] = f.read().splitlines()
                        scroll_offsets[1] = 0
                        cursor_pos[1] = 0
                except Exception as e:
                    show_message(stdscr, f" Error: {str(e)[:w-10]} ", 3)
        
        elif key == curses.KEY_F3:
            if not buffers[0] and not buffers[1]:
                show_message(stdscr, " Both buffers empty! ", 2)
                continue
            
            diff_lines = list(difflib.unified_diff(buffers[0], buffers[1],
                                                  fromfile='buffer_a', tofile='buffer_b',
                                                  lineterm=''))
            if not diff_lines:
                show_message(stdscr, " No differences found ", 2)
                continue
            
            diff_scroll = 0
            merge_mode = False
            
            while True:
                actual_start = display_diff(stdscr, buffers, diff_lines, diff_scroll, merge_mode)
                
                subkey = stdscr.getch()
                
                if subkey == 27:
                    break
                elif subkey == ord('v') or subkey == ord('V'):
                    merge_mode = not merge_mode
                    diff_scroll = 0
                elif subkey == curses.KEY_UP:
                    if diff_scroll > 0:
                        diff_scroll -= 1
                elif subkey == curses.KEY_DOWN:
                    display_lines = generate_merge_view(buffers) if merge_mode else diff_lines
                    max_scroll = max(0, len(display_lines) - 1)
                    if diff_scroll < max_scroll:
                        diff_scroll += 1
                elif subkey == curses.KEY_PPAGE:
                    diff_scroll = max(0, diff_scroll - (h - 4))
                elif subkey == curses.KEY_NPAGE:
                    display_lines = generate_merge_view(buffers) if merge_mode else diff_lines
                    max_scroll = max(0, len(display_lines) - 1)
                    diff_scroll = min(max_scroll, diff_scroll + (h - 4))
                elif subkey == curses.KEY_HOME:
                    diff_scroll = 0
                elif subkey == curses.KEY_END:
                    display_lines = generate_merge_view(buffers) if merge_mode else diff_lines
                    diff_scroll = max(0, len(display_lines) - 1)
                # F4 intentionally ignored in diff view
            
            stdscr.touchwin()
            
        elif key == curses.KEY_F4:
            # ORIGINAL WORKING F4 METHOD
            default_path = get_default_export_path()
            target = get_input(stdscr, "Export To: ", default_text=default_path)
            if target is None:
                show_message(stdscr, " Cancelled ", 1)
            elif target:
                target = os.path.expanduser(target)
                fmt = get_input(stdscr, "Format (raw/context/ndiff/merge) [raw]: ")
                if fmt is None:
                    show_message(stdscr, " Cancelled ", 1)
                else:
                    fmt = fmt.lower() or 'raw'
                    try:
                        diff = export_diff(buffers, target, fmt)
                        show_message(stdscr, f" Saved {len(diff)} lines ", 2)
                    except Exception as e:
                        show_message(stdscr, f" Save failed: {str(e)[:w-15]} ", 3)
        
        # Two-stage delete: 1.5s hold starts countdown, then 1.5s countdown clears
        elif key in [curses.KEY_DC, 127, curses.KEY_BACKSPACE]:
            if buffers[focus]:
                # Start the 1.5s hold detection
                delete_first_press_time = time.time()
            else:
                # Normal backspace when empty (nothing to do)
                pass
        
        elif key == curses.KEY_UP:
            if scroll_offsets[focus] > 0:
                scroll_offsets[focus] -= 1
        elif key == curses.KEY_DOWN:
            max_scroll = max(0, len(buffers[focus]) - (content_h - 2))
            if scroll_offsets[focus] < max_scroll:
                scroll_offsets[focus] += 1
        elif key == curses.KEY_PPAGE:
            scroll_offsets[focus] = max(0, scroll_offsets[focus] - (content_h - 2))
        elif key == curses.KEY_NPAGE:
            max_scroll = max(0, len(buffers[focus]) - (content_h - 2))
            scroll_offsets[focus] = min(max_scroll, scroll_offsets[focus] + (content_h - 2))
        elif key == curses.KEY_HOME:
            scroll_offsets[focus] = 0
        elif key == curses.KEY_END:
            max_scroll = max(0, len(buffers[focus]) - (content_h - 2))
            scroll_offsets[focus] = max_scroll
        elif key == 10 or key == 13:
            buffers[focus].append("")
            scroll_offsets[focus] = max(0, len(buffers[focus]) - (content_h - 2))
            cursor_pos[focus] = 0
        elif 32 <= key <= 126:
            if not buffers[focus]:
                buffers[focus].append("")
            buffers[focus][-1] += chr(key)
            cursor_pos[focus] += 1

if __name__ == "__main__":
    try:
        curses.wrapper(main)
    except KeyboardInterrupt:
        sys.exit(0)
