#!/usr/bin/env python3
"""
Download p5js collection sketches for all 2025-2026 students.
Reads collection.json from each student folder, fetches via p5js API,
saves files to local p5/ subfolder.
"""

import json
import os
import re
import shutil
import time
import urllib.request

BASE_DIR = os.path.join(os.path.dirname(__file__), "Data", "Leerlingen", "2025-2026")
API_BASE = "https://editor.p5js.org/editor"

def fetch_json(url):
    req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
    with urllib.request.urlopen(req, timeout=15) as r:
        return json.loads(r.read().decode("utf-8"))

def sanitize_folder_name(name):
    """Make a safe folder name from sketch name."""
    name = re.sub(r'[<>:"/\\|?*]', '_', name)
    name = name.strip('. ')
    return name[:80] if name else "untitled"

def download_sketch(username, sketch_id, sketch_name, dest_folder):
    url = f"{API_BASE}/{username}/projects/{sketch_id}"
    try:
        data = fetch_json(url)
    except Exception as e:
        print(f"    ERROR fetching sketch {sketch_id}: {e}")
        return

    folder_name = sanitize_folder_name(sketch_name)
    sketch_dir = os.path.join(dest_folder, folder_name)
    os.makedirs(sketch_dir, exist_ok=True)

    files = data.get("files", [])
    saved = []
    for f in files:
        if f.get("fileType") == "folder":
            continue
        fname = f.get("name", "")
        content = f.get("content", "")
        if not fname or not content:
            continue
        fpath = os.path.join(sketch_dir, fname)
        with open(fpath, "w", encoding="utf-8") as out:
            out.write(content)
        saved.append(fname)

    # Zorg dat style.css de canvas centreert
    css_path = os.path.join(sketch_dir, "style.css")
    apply_centering_css(css_path)

    print(f"    Saved '{folder_name}': {saved}")

def apply_centering_css(css_path):
    """Voeg flexbox-centrering toe aan style.css als die nog ontbreekt."""
    centering = "display:flex;justify-content:center;align-items:center;min-height:100vh;"
    if os.path.isfile(css_path):
        content = open(css_path, encoding="utf-8").read()
        if "justify-content" not in content:
            content = content.replace(
                "html, body {",
                "html, body {\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  min-height: 100vh;"
            )
            with open(css_path, "w", encoding="utf-8") as f:
                f.write(content)
    else:
        with open(css_path, "w", encoding="utf-8") as f:
            f.write("html, body {\n  margin: 0;\n  padding: 0;\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  min-height: 100vh;\n}\ncanvas { display: block; }\n")

def process_student(class_name, student_name, student_dir):
    collection_file = os.path.join(student_dir, "collection.json")
    if not os.path.isfile(collection_file):
        print(f"  [{student_name}] Geen collection.json, overgeslagen.")
        return

    with open(collection_file, encoding="utf-8") as f:
        data = json.load(f)

    url = data.get("collection_url", "")
    # Parse username and collection ID from URL
    # Format: https://editor.p5js.org/{username}/collections/{collectionId}
    m = re.search(r"editor\.p5js\.org/([^/]+)/collections/([^/?]+)", url)
    if not m:
        print(f"  [{student_name}] Kan URL niet parsen: {url}")
        return

    username, collection_id = m.group(1), m.group(2)
    print(f"\n[{student_name}] ({username}) — collectie: {collection_id}")

    # Fetch all collections for user, find matching one
    try:
        collections = fetch_json(f"{API_BASE}/{username}/collections")
    except Exception as e:
        print(f"  ERROR: {e}")
        return

    collection = next((c for c in collections if c["_id"] == collection_id), None)
    if not collection:
        print(f"  Collectie {collection_id} niet gevonden (of leeg).")
        return

    items = collection.get("items", [])
    print(f"  {len(items)} sketch(es) gevonden in collectie '{collection['name']}'")

    p5_dir = os.path.join(student_dir, "p5")
    os.makedirs(p5_dir, exist_ok=True)

    expected_folders = set()
    for item in items:
        project = item.get("project", {})
        sketch_id = project.get("_id", "")
        sketch_name = project.get("name", sketch_id)
        folder_name = sanitize_folder_name(sketch_name)
        expected_folders.add(folder_name)
        print(f"  -> {sketch_name}")
        download_sketch(username, sketch_id, sketch_name, p5_dir)
        time.sleep(0.3)  # be gentle on the API

    # Verwijder mappen die niet meer in de collectie zitten
    for existing in os.listdir(p5_dir):
        existing_path = os.path.join(p5_dir, existing)
        if os.path.isdir(existing_path) and existing not in expected_folders:
            shutil.rmtree(existing_path)
            print(f"  Verwijderd (niet meer in collectie): {existing}")


def main():
    for class_name in sorted(os.listdir(BASE_DIR)):
        class_dir = os.path.join(BASE_DIR, class_name)
        if not os.path.isdir(class_dir):
            continue
        print(f"\n=== Klas {class_name} ===")
        for student_name in sorted(os.listdir(class_dir)):
            student_dir = os.path.join(class_dir, student_name)
            if not os.path.isdir(student_dir):
                continue
            process_student(class_name, student_name, student_dir)

    print("\n\nKlaar!")

if __name__ == "__main__":
    main()
