#!/usr/bin/env bash

set -euo pipefail

if [[ $# -ne 3 ]]; then
    printf 'Usage: %s REFERENCE_IMAGE CURRENT_IMAGE OUTPUT_DIRECTORY\n' "$0" >&2
    exit 64
fi

reference_image="$1"
current_image="$2"
output_directory="$3"

if ! command -v magick >/dev/null 2>&1; then
    printf 'ImageMagick is required but the magick command was not found.\n' >&2
    exit 69
fi

for image in "$reference_image" "$current_image"; do
    if [[ ! -f "$image" ]]; then
        printf 'Image not found: %s\n' "$image" >&2
        exit 66
    fi
done

mkdir -p "$output_directory"

reference_width="$(magick identify -format '%w' "$reference_image")"
reference_height="$(magick identify -format '%h' "$reference_image")"

magick "$current_image" \
    -resize "${reference_width}x" \
    "$output_directory/current-scaled.png"

current_height="$(magick identify -format '%h' "$output_directory/current-scaled.png")"
canvas_height="$reference_height"

if (( current_height > reference_height )); then
    canvas_height="$current_height"
fi

magick "$reference_image" \
    -background '#0b071c' \
    -gravity north \
    -extent "${reference_width}x${canvas_height}" \
    "$output_directory/reference-normalized.png"

magick "$output_directory/current-scaled.png" \
    -background '#0b071c' \
    -gravity north \
    -extent "${reference_width}x${canvas_height}" \
    "$output_directory/current-normalized.png"

magick \
    "$output_directory/reference-normalized.png" \
    "$output_directory/current-normalized.png" \
    +append \
    "$output_directory/side-by-side.png"

magick \
    "$output_directory/reference-normalized.png" \
    "$output_directory/current-normalized.png" \
    -define compose:args=50,50 \
    -compose blend \
    -composite \
    "$output_directory/overlay.png"

magick \
    "$output_directory/reference-normalized.png" \
    "$output_directory/current-normalized.png" \
    -compose difference \
    -composite \
    "$output_directory/difference.png"

printf 'Created:\n'
printf '  %s\n' "$output_directory/side-by-side.png"
printf '  %s\n' "$output_directory/overlay.png"
printf '  %s\n' "$output_directory/difference.png"
