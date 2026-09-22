import os
import math
from PIL import Image, ImageDraw, ImageFont

public_images = r"c:\xampp\htdocs\expense-tracker2\public\images"
os.makedirs(public_images, exist_ok=True)

def create_gradient_bg(width, height, color1, color2):
    base = Image.new('RGBA', (width, height), color1)
    top = Image.new('RGBA', (width, height), color2)
    mask = Image.new('L', (width, height))
    mask_data = []
    for y in range(height):
        for x in range(width):
            # Diagonal gradient
            factor = (x / width + y / height) / 2.0
            mask_data.append(int(255 * factor))
    mask.putdata(mask_data)
    base.paste(top, (0, 0), mask)
    return base

def draw_kharchify_logo(img, size, padding_ratio=0.20, is_maskable=False):
    # If maskable, safe zone is inner 80% circle
    pad = int(size * (0.22 if is_maskable else 0.15))
    w = size - 2 * pad
    h = size - 2 * pad
    
    draw = ImageDraw.Draw(img)
    
    # Base coordinate mappings (0..100) -> (pad..pad+w, pad..pad+h)
    def pt(x, y):
        return (pad + int(x * w / 100), pad + int(y * h / 100))
    
    # 1. Left parallelogram bar
    poly1 = [pt(12, 78), pt(32, 78), pt(50, 44), pt(30, 44)]
    draw.polygon(poly1, fill=(5, 150, 105, 255)) # emerald-600
    
    # 2. Middle main slope body
    poly2 = [pt(36, 78), pt(56, 78), pt(84, 26), pt(64, 26)]
    draw.polygon(poly2, fill=(16, 185, 129, 255)) # emerald-500
    
    # 3. Bottom dark triangle facet
    poly3 = [pt(60, 78), pt(88, 78), pt(74, 54)]
    draw.polygon(poly3, fill=(6, 95, 70, 255)) # emerald-800
    
    # 4. Arrow Body connection
    poly4 = [pt(48, 56), pt(68, 56), pt(82, 30), pt(62, 30)]
    draw.polygon(poly4, fill=(52, 211, 153, 235)) # emerald-400
    
    # 5. Top rising arrow head
    poly5 = [pt(52, 38), pt(94, 38), pt(84, 8)]
    draw.polygon(poly5, fill=(110, 231, 183, 255)) # emerald-300

def generate_icon(size, is_maskable=False):
    if is_maskable:
        # Solid background without rounded corners (Android applies mask)
        img = create_gradient_bg(size, size, (15, 23, 42, 255), (6, 78, 59, 255)) # Slate-900 to Emerald-900
        draw_kharchify_logo(img, size, is_maskable=True)
    else:
        # Rounded squircle background
        img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
        bg = create_gradient_bg(size, size, (15, 23, 42, 255), (6, 78, 59, 255))
        
        # Rounded corner mask
        mask = Image.new('L', (size, size), 0)
        mask_draw = ImageDraw.Draw(mask)
        radius = int(size * 0.22)
        mask_draw.rounded_rectangle([0, 0, size, size], radius=radius, fill=255)
        
        img.paste(bg, (0, 0), mask)
        draw_kharchify_logo(img, size, is_maskable=False)
        
    return img

def generate_screenshot(width, height, is_mobile=True):
    img = create_gradient_bg(width, height, (15, 23, 42, 255), (2, 44, 34, 255))
    draw = ImageDraw.Draw(img)
    
    # Header card
    if is_mobile:
        draw.rounded_rectangle([40, 60, width - 40, 220], radius=24, fill=(30, 41, 59, 255), outline=(16, 185, 129, 120), width=2)
        draw.rounded_rectangle([40, 260, width - 40, 520], radius=24, fill=(16, 185, 129, 255))
        draw.rounded_rectangle([40, 560, width - 40, height - 80], radius=24, fill=(30, 41, 59, 255))
    else:
        draw.rounded_rectangle([60, 60, width - 60, 160], radius=20, fill=(30, 41, 59, 255), outline=(16, 185, 129, 120), width=2)
        draw.rounded_rectangle([60, 200, 400, height - 60], radius=20, fill=(30, 41, 59, 255))
        draw.rounded_rectangle([440, 200, width - 60, 460], radius=20, fill=(16, 185, 129, 255))
        draw.rounded_rectangle([440, 500, width - 60, height - 60], radius=20, fill=(30, 41, 59, 255))
        
    return img

# Generate icons
icon_512 = generate_icon(512, is_maskable=False)
icon_512.save(os.path.join(public_images, "icon-512.png"))

icon_192 = generate_icon(192, is_maskable=False)
icon_192.save(os.path.join(public_images, "icon-192.png"))

icon_maskable_512 = generate_icon(512, is_maskable=True)
icon_maskable_512.save(os.path.join(public_images, "icon-maskable-512.png"))

icon_maskable_192 = generate_icon(192, is_maskable=True)
icon_maskable_192.save(os.path.join(public_images, "icon-maskable-192.png"))

# Screenshots for PWABuilder / PlayStore
screenshot_m = generate_screenshot(720, 1280, is_mobile=True)
screenshot_m.save(os.path.join(public_images, "screenshot-mobile.png"))

screenshot_d = generate_screenshot(1280, 720, is_mobile=False)
screenshot_d.save(os.path.join(public_images, "screenshot-desktop.png"))

print("Successfully generated all PWA icons and screenshots!")
