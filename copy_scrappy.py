import os
import pyperclip

# Define extensions to exclude
EXCLUDED_EXTENSIONS = {'.png', '.jpg', '.jpeg', '.gif', '.bmp', '.mp4', '.avi', '.mov', '.mkv', '.mp3', '.wav', '.flac'}

def read_all_files(directory, exclude_file, specific_files=None, exclude_items=None):
    """Reads all files in the given directory and copies the contents to the clipboard.
    Skips the 'node_modules' directory.

    Args:
        directory: The directory to read files from.
        exclude_file: The name of the script file to exclude.
        specific_files: A comma-separated string of files and folders to read.
                        If None, all files are read (except those specified for exclusion).
        exclude_items: A comma-separated string of files and folders to exclude from reading.
    """
    file_contents = []

    if specific_files:
        items_to_read = [item.strip() for item in specific_files.split(',')]
    else:
        items_to_read = None  # Read all files by default

    if exclude_items:
        items_to_exclude = [item.strip() for item in exclude_items.split(',')]
    else:
        items_to_exclude = []  # Exclude nothing by default

    # Traverse the directory
    for root, dirs, files in os.walk(directory):
        # Exclude 'node_modules' before processing subdirectories
        if 'node_modules' in dirs:
            dirs.remove('node_modules')

        # Check if the current folder or its subfolders match any in items_to_read
        if items_to_read:
            for item in items_to_read:
                # Check if the item is a file in the current directory
                if os.path.isfile(os.path.join(root, item)):
                    file_path = os.path.join(root, item)

                    # Exclude the script file itself
                    if item == exclude_file:
                        continue

                    # Exclude files with certain extensions
                    if any(item.lower().endswith(ext) for ext in EXCLUDED_EXTENSIONS):
                        continue

                    try:
                        with open(file_path, 'r') as f:
                            content = f.read()
                            relative_path = os.path.relpath(file_path, directory)
                            file_contents.append(f"{relative_path}\n{content}")
                    except (IOError, OSError, UnicodeDecodeError) as e:
                        print(f"Error reading {file_path}: {e}")
                        continue

                # Check if the item is a folder and the current path is within it
                elif item in os.path.relpath(root, directory).split(os.sep):
                    # Found a matching folder, process files within
                    for file in files:
                        file_path = os.path.join(root, file)

                        # Exclude the script file itself
                        if file == exclude_file:
                            continue

                        # Exclude files with certain extensions
                        if any(file.lower().endswith(ext) for ext in EXCLUDED_EXTENSIONS):
                            continue

                        try:
                            with open(file_path, 'r') as f:
                                content = f.read()
                                relative_path = os.path.relpath(file_path, directory)
                                file_contents.append(f"{relative_path}\n{content}")
                        except (IOError, OSError, UnicodeDecodeError) as e:
                            print(f"Error reading {file_path}: {e}")
                            continue
                    break  # Move on to the next directory in os.walk

        # If no specific files, process all (excluding those in exclude_items)
        elif not items_to_read:
            # Skip excluded directories
            dirs[:] = [d for d in dirs if d not in items_to_exclude]

            for file in files:
                file_path = os.path.join(root, file)

                # Exclude the script file itself
                if file == exclude_file:
                    continue

                # Exclude files with certain extensions
                if any(file.lower().endswith(ext) for ext in EXCLUDED_EXTENSIONS):
                    continue

                try:
                    with open(file_path, 'r') as f:
                        content = f.read()
                        relative_path = os.path.relpath(file_path, directory)
                        file_contents.append(f"{relative_path}\n{content}")
                except (IOError, OSError, UnicodeDecodeError) as e:
                    print(f"Error reading {file_path}: {e}")
                    continue

    # Combine all contents into one string
    combined_contents = '\n'.join(file_contents)

    # Copy to clipboard
    try:
        pyperclip.copy(combined_contents)
        print("Contents copied to clipboard.")
    except pyperclip.PyperclipException as e:
        print(f"Error copying to clipboard: {e}")


if __name__ == "__main__":
    # Get the directory where the script is placed
    script_directory = os.path.dirname(os.path.abspath(__file__))
    script_name = os.path.basename(__file__)

    while True:
        print("Choose an option:")
        print("1. Read specific files and folders")
        print("2. Exclude files and folders")
        print("3. Read all files (default)")

        choice = input("Enter option number: ")

        if choice == '1':
            specific_files = input("Enter comma-separated folder names: ")
            read_all_files(script_directory, script_name, specific_files=specific_files)
        elif choice == '2':
            exclude_items = input("Enter comma-separated files and folders to exclude: ")
            read_all_files(script_directory, script_name, exclude_items=exclude_items)
        else:
            read_all_files(script_directory, script_name)