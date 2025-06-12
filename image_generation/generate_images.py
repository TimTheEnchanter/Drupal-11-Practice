from PIL import Image, ImageDraw, ImageFont
import os

def generate_test_images(num_images=122, output_dir="test_images"):
    """
    Generates a specified number of basic JPG images with text.

    Args:
        num_images (int): The total number of images to generate.
        output_dir (str): The directory where the images will be saved.
    """
    # Create the output directory if it doesn't exist
    os.makedirs(output_dir, exist_ok=True)

    # Image settings
    width, height = 150, 150
    background_color = (204, 224, 255)  # Light blue (CCE0FF)
    text_color = (0, 0, 0)             # Black (000000)

    # Try to load a default font, fall back to a generic one if not found
    try:
        # This path might vary based on your OS. Adjust if 'arial.ttf' isn't found.
        font = ImageFont.truetype("arial.ttf", 16)
    except IOError:
        print("Warning: 'arial.ttf' not found. Using default PIL font (may look different).")
        font = ImageFont.load_default()

    print(f"Generating {num_images} images in '{output_dir}'...")

    for i in range(1, num_images + 1):
        # Format the number with leading zeros (e.g., 1 -> 0001, 12 -> 0012)
        padded_number = str(i).zfill(4)
        filename = f"IMG_{padded_number}.jpg"
        filepath = os.path.join(output_dir, filename)
        display_text = filename

        # Create a new image with the specified background color
        img = Image.new('RGB', (width, height), color=background_color)
        d = ImageDraw.Draw(img)

        # Calculate text position to center it
        bbox = d.textbbox((0, 0), display_text, font=font)
        text_width = bbox[2] - bbox[0]
        text_height = bbox[3] - bbox[1]
        x = (width - text_width) / 2
        y = (height - text_height) / 2

        # Draw the text on the image
        d.text((x, y), display_text, fill=text_color, font=font)

        # Save the image as a JPG
        img.save(filepath, "JPEG")
        print(f"Created: {filepath}")

    print(f"\nSuccessfully generated {num_images} images.")

if __name__ == "__main__":
    generate_test_images()
