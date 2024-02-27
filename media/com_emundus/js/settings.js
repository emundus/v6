/** Function to check contrast between two colors **/
const RED = 0.2126;
const GREEN = 0.7152;
const BLUE = 0.0722;

const GAMMA = 2.4;

const hexToRgb = hex =>
    hex.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i
        ,(m, r, g, b) => '#' + r + r + g + g + b + b)
        .substring(1).match(/.{2}/g)
        .map(x => parseInt(x, 16));

function luminance(r, g, b) {
    var a = [r, g, b].map((v) => {
        v /= 255;
        return v <= 0.03928
            ? v / 12.92
            : Math.pow((v + 0.055) / 1.055, GAMMA);
    });
    return a[0] * RED + a[1] * GREEN + a[2] * BLUE;
}

function contrast(rgb1, rgb2) {
    var lum1 = luminance(...rgb1);
    var lum2 = luminance(...rgb2);
    var brightest = Math.max(lum1, lum2);
    var darkest = Math.min(lum1, lum2);
    return (brightest + 0.05) / (darkest + 0.05);
}

function checkSimilarity(hex1, hex2, container) {
    if(document.querySelector("#similarity_error")) {
        document.querySelector("#similarity_error").remove();
    }

    let rgb1 = hexToRgb(hex1);
    let rgb2 = hexToRgb(hex2);
    const deltaECalc = deltaE(rgb1, rgb2);

    if(deltaECalc < 11) {
        if(container) {
            let p = document.createElement("p");
            p.id = "similarity_error";
            p.classList.add("text-red-500");
            p.innerHTML = Joomla.JText._('COM_EMUNDUS_ONBOARD_ERROR_COLORS_SAME');
            document.querySelector(container).append(p);
        } else {
            return false;
        }
    }

    return true;
}

function checkContrast(hex1, hex2, container) {
    if(document.querySelector("#contrast_error")) {
        document.querySelector("#contrast_error").remove();
    }

    let rgb1 = hexToRgb(hex1);
    let rgb2 = hexToRgb(hex2);
    const contrastCalc = contrast(rgb1, rgb2);

    if(contrastCalc < 3.1) {
        if(container) {
            let p = document.createElement("p");
            p.id = "contrast_error";
            p.classList.add("text-red-500");
            p.innerHTML = Joomla.JText._('COM_EMUNDUS_SETTINGS_CONTRAST_ERROR');
            document.querySelector(container).append(p);
        } else {
            return false;
        }
    }

    return true;
}

/* Function to check if two colors are similar */
function deltaE(rgbA, rgbB) {
    let labA = rgb2lab(rgbA);
    let labB = rgb2lab(rgbB);
    let deltaL = labA[0] - labB[0];
    let deltaA = labA[1] - labB[1];
    let deltaB = labA[2] - labB[2];
    let c1 = Math.sqrt(labA[1] * labA[1] + labA[2] * labA[2]);
    let c2 = Math.sqrt(labB[1] * labB[1] + labB[2] * labB[2]);
    let deltaC = c1 - c2;
    let deltaH = deltaA * deltaA + deltaB * deltaB - deltaC * deltaC;
    deltaH = deltaH < 0 ? 0 : Math.sqrt(deltaH);
    let sc = 1.0 + 0.045 * c1;
    let sh = 1.0 + 0.015 * c1;
    let deltaLKlsl = deltaL / (1.0);
    let deltaCkcsc = deltaC / (sc);
    let deltaHkhsh = deltaH / (sh);
    let i = deltaLKlsl * deltaLKlsl + deltaCkcsc * deltaCkcsc + deltaHkhsh * deltaHkhsh;
    return i < 0 ? 0 : Math.sqrt(i);
}

function rgb2lab(rgb){
    let r = rgb[0] / 255, g = rgb[1] / 255, b = rgb[2] / 255, x, y, z;
    r = (r > 0.04045) ? Math.pow((r + 0.055) / 1.055, 2.4) : r / 12.92;
    g = (g > 0.04045) ? Math.pow((g + 0.055) / 1.055, 2.4) : g / 12.92;
    b = (b > 0.04045) ? Math.pow((b + 0.055) / 1.055, 2.4) : b / 12.92;
    x = (r * 0.4124 + g * 0.3576 + b * 0.1805) / 0.95047;
    y = (r * 0.2126 + g * 0.7152 + b * 0.0722) / 1.00000;
    z = (r * 0.0193 + g * 0.1192 + b * 0.9505) / 1.08883;
    x = (x > 0.008856) ? Math.pow(x, 1/3) : (7.787 * x) + 16/116;
    y = (y > 0.008856) ? Math.pow(y, 1/3) : (7.787 * y) + 16/116;
    z = (z > 0.008856) ? Math.pow(z, 1/3) : (7.787 * z) + 16/116;
    return [(116 * y) - 16, 500 * (x - y), 200 * (y - z)]
}