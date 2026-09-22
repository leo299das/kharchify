import os
from PIL import Image, ImageDraw

public_images = r"c:\xampp\htdocs\expense-tracker2\public\images"
public_dir = r"c:\xampp\htdocs\expense-tracker2\public"
os.makedirs(public_images, exist_ok=True)

def create_gradient_bg(width, height, color1, color2):
    base = Image.new('RGBA', (width, height), color1)
    top = Image.new('RGBA', (width, height), color2)
    mask = Image.new('L', (width, height))
    mask_data = []
    for y in range(height):
        for x in range(width):
            factor = (x / width + y / height) / 2.0
            mask_data.append(int(255 * factor))
    mask.putdata(mask_data)
    base.paste(top, (0, 0), mask)
    return base

def draw_kharchify_logo(img, size, is_maskable=False):
    pad = int(size * (0.22 if is_maskable else 0.16))
    w = size - 2 * pad
    h = size - 2 * pad
    
    draw = ImageDraw.Draw(img)
    
    def pt(x, y):
        return (pad + int(x * w / 100), pad + int(y * h / 100))
    
    # 1. Left parallelogram bar
    poly1 = [pt(12, 78), pt(32, 78), pt(50, 44), pt(30, 44)]
    draw.polygon(poly1, fill=(5, 150, 105, 255))
    
    # 2. Middle main slope body
    poly2 = [pt(36, 78), pt(56, 78), pt(84, 26), pt(64, 26)]
    draw.polygon(poly2, fill=(16, 185, 129, 255))
    
    # 3. Bottom dark triangle facet
    poly3 = [pt(60, 78), pt(88, 78), pt(74, 54)]
    draw.polygon(poly3, fill=(6, 95, 70, 255))
    
    # 4. Arrow Body connection
    poly4 = [pt(48, 56), pt(68, 56), pt(82, 30), pt(62, 30)]
    draw.polygon(poly4, fill=(52, 211, 153, 235))
    
    # 5. Top rising arrow head
    poly5 = [pt(52, 38), pt(94, 38), pt(84, 8)]
    draw.polygon(poly5, fill=(110, 231, 183, 255))

def generate_icon(size, is_maskable=False):
    if is_maskable:
        img = create_gradient_bg(size, size, (15, 23, 42, 255), (6, 78, 59, 255))
        draw_kharchify_logo(img, size, is_maskable=True)
    else:
        img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
        bg = create_gradient_bg(size, size, (15, 23, 42, 255), (6, 78, 59, 255))
        mask = Image.new('L', (size, size), 0)
        mask_draw = ImageDraw.Draw(mask)
        radius = int(size * 0.22)
        mask_draw.rounded_rectangle([0, 0, size, size], radius=radius, fill=255)
        img.paste(bg, (0, 0), mask)
        draw_kharchify_logo(img, size, is_maskable=False)
    return img

# Standard Android / PWA Icon Sizes
sizes = [72, 96, 128, 144, 152, 192, 384, 512]
for s in sizes:
    # Standard
    icon = generate_icon(s, is_maskable=False)
    icon.save(os.path.join(public_images, f"icon-{s}.png"))
    
    # Maskable
    icon_m = generate_icon(s, is_maskable=True)
    icon_m.save(os.path.join(public_images, f"icon-maskable-{s}.png"))

# Favicon PNGs and Apple Touch Icon
generate_icon(180, is_maskable=False).save(os.path.join(public_dir, "apple-touch-icon.png"))
generate_icon(180, is_maskable=False).save(os.path.join(public_images, "apple-touch-icon.png"))
generate_icon(32, is_maskable=False).save(os.path.join(public_images, "favicon-32x32.png"))
generate_icon(16, is_maskable=False).save(os.path.join(public_images, "favicon-16x16.png"))

print("All Android & Web icon sizes generated successfully!")
