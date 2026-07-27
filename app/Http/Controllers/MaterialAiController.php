<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaterialAiController extends Controller
{
    public function generateSpecs(Request $request)
    {
        $name = trim($request->input('name', ''));
        $unit = trim($request->input('unit', ''));

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุชื่อรายการวัสดุ',
            ], 422);
        }

        $apiKey = config('services.gemini.key');

        // Try calling Gemini API if API key is configured
        if (!empty($apiKey)) {
            try {
                $response = Http::timeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => "คุณคือระบบ AI ผู้เชี่ยวชาญด้านงานพัสดุและครุภัณฑ์สำนักงานของหน่วยงานราชการไทย โปรดระบุ 'ขนาดหรือลักษณะ / สเปกรายละเอียด' สั้นๆ กระชับ ชัดเจน สำหรับรายการวัสดุชื่อ: '{$name}' เพื่อใช้ในสมุดบัญชีคุมพัสดุราชการ ตอบเฉพาะข้อความสเปกภาษาไทย ความยาว 1-2 ประโยค ห้ามใส่คำเกริ่น ห้ามใส่หัวข้อ และห้ามใส่เครื่องหมายอัญประกาศ"
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($text)) {
                        $cleanText = trim(str_replace(['"', "'", "```"], '', $text));
                        return response()->json([
                            'success' => true,
                            'specs' => $cleanText,
                            'source' => 'gemini_api',
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Gemini API spec generation failed: ' . $e->getMessage());
            }
        }

        // Fallback to Smart Thai Office Material Spec Generator
        $smartResult = $this->getSmartMaterialSpecs($name, $unit);

        return response()->json([
            'success' => true,
            'specs' => $smartResult['specs'],
            'unit' => $smartResult['unit'],
            'source' => 'smart_engine',
        ]);
    }

    private function getSmartMaterialSpecs(string $name, string $unit)
    {
        $n = mb_strtolower($name, 'UTF-8');

        // Dynamic Size Extractions
        preg_match('/(\d+(?:\.\d+)?)\s*(?:นิ้ว|")/u', $name, $inchMatch);
        $sizeInch = isset($inchMatch[1]) ? $inchMatch[1] . ' นิ้ว' : null;

        preg_match('/(\d+)\s*แกรม/u', $name, $gsmMatch);
        $sizeGsm = isset($gsmMatch[1]) ? $gsmMatch[1] . ' แกรม' : null;

        preg_match('/(\d+(?:\.\d+)?)\s*มม\.?/u', $name, $mmMatch);
        $sizeMm = isset($mmMatch[1]) ? $mmMatch[1] . ' มม.' : null;

        preg_match('/(\d+)\s*(?:มล\.?|มิลลิลิตร|ml)/ui', $name, $mlMatch);
        $sizeMl = isset($mlMatch[1]) ? $mlMatch[1] . ' มล.' : null;

        $autoUnit = $unit;

        // 1. สมุดลงเวลา / สมุดประเภทต่างๆ
        if (mb_strpos($n, 'สมุด') !== false) {
            if (mb_strpos($n, 'ลงเวลา') !== false || mb_strpos($n, 'ลงนาม') !== false) {
                $specs = 'ขนาด A4 (210 x 297 มม.) ปกแข็งหุ้มอย่างดี สมุดลงเวลาปฏิบัติราชการของข้าราชการและบุคลากร ด้านในมีตารางระบุวันที่ ชื่อ-นามสกุล เวลามา-เวลากลับ และช่องลงลายมือชื่อ';
                if (empty($autoUnit)) $autoUnit = 'เล่ม';
            } elseif (mb_strpos($n, 'ส่งหนังสือ') !== false || mb_strpos($n, 'รับหนังสือ') !== false || mb_strpos($n, 'ทะเบียน') !== false) {
                $specs = 'ขนาด 210 x 330 มม. ปกแข็งอย่างดี พิมพ์ตารางลงทะเบียนรับ-ส่งหนังสือราชการคมชัด กระดาษปอนด์ 70 แกรม บรรจุ 80 แผ่น/เล่ม';
                if (empty($autoUnit)) $autoUnit = 'เล่ม';
            } elseif (mb_strpos($n, 'เบิกพัสดุ') !== false || mb_strpos($n, 'คุมพัสดุ') !== false || mb_strpos($n, 'บัญชีพัสดุ') !== false) {
                $specs = 'ขนาด 210 x 330 มม. ปกแข็งหุ้มอย่างดี พิมพ์ตารางคุมรับ-จ่าย-คงเหลือพัสดุตามระเบียบกระทรวงการคลัง บรรจุ 100 แผ่น/เล่ม';
                if (empty($autoUnit)) $autoUnit = 'เล่ม';
            } elseif (mb_strpos($n, 'บัญชี') !== false || mb_strpos($n, 'เงินสด') !== false || mb_strpos($n, 'รายวัน') !== false) {
                $specs = 'ขนาด A4 ปกแข็งอย่างดี พิมพ์ตารางบัญชีการเงินคมชัด กระดาษปอนด์ 70 แกรม บรรจุ 100 แผ่น/เล่ม';
                if (empty($autoUnit)) $autoUnit = 'เล่ม';
            } else {
                $specs = 'ขนาด A4 (210 x 297 มม.) ปกแข็งอย่างดี เนื้อกระดาษปอนด์ขาว 70 แกรม พิมพ์เส้นบรรทัดชัดเจน บรรจุ 80 แผ่น/เล่ม';
                if (empty($autoUnit)) $autoUnit = 'เล่ม';
            }
        }
        // 2. กรรไกร
        elseif (mb_strpos($n, 'กรรไกร') !== false) {
            $sz = $sizeInch ?? '8 นิ้ว';
            $specs = "ขนาด {$sz} ใบมีดสแตนเลสคุณภาพสูง คมทนทาน ด้ามจับหุ้มยางนุ่มกระชับมือ ตัดง่าย เหมาะสำหรับงานตัดกระดาษและเอกสารสำนักงาน";
            if (empty($autoUnit)) $autoUnit = 'เล่ม';
        }
        // 3. ปากกา
        elseif (mb_strpos($n, 'ปากกา') !== false) {
            $tip = $sizeMm ?? '0.5 มม.';
            if (mb_strpos($n, 'เน้นข้อความ') !== false || mb_strpos($n, 'ไฮไลท์') !== false) {
                $specs = 'หัวหมึกชนิดตัด ขนาดเส้น 2-5 มม. หมึกสีสดใส ไม่ซีดจาง ไม่ไร้รอยซึมหลังกระดาษ';
                if (empty($autoUnit)) $autoUnit = 'ด้าม';
            } elseif (mb_strpos($n, 'ไวท์บอร์ด') !== false) {
                $specs = 'หัวลบได้ กลิ่นไม่ฉุน เขียนลื่น ลบง่าย ไม่ทิ้งคราบสกปรกบนกระดานไวท์บอร์ด';
                if (empty($autoUnit)) $autoUnit = 'ด้าม';
            } elseif (mb_strpos($n, 'เคมี') !== false || mb_strpos($n, 'เมจิก') !== false) {
                $specs = 'หมึกกันน้ำ กลิ่นไม่ฉุน เขียนได้บนทุกพื้นผิว แห้งเร็ว สีเข้มคมชัด';
                if (empty($autoUnit)) $autoUnit = 'ด้าม';
            } else {
                $specs = "ขนาดหัวเขียน {$tip} หมึกไหลสม่ำเสมอ เขียนลื่น แห้งไว ไม่เลอะเทอะ ด้ามจับกระชับมือ";
                if (empty($autoUnit)) $autoUnit = 'ด้าม';
            }
        }
        // 4. ดินสอ / ยางลบ / กบเหลา
        elseif (mb_strpos($n, 'ดินสอ') !== false) {
            if (mb_strpos($n, 'กด') !== false) {
                $specs = "ขนาดไส้ " . ($sizeMm ?? '0.5 มม.') . " ด้ามจับกระชับมือ มีคลิปหนีบ ปลายหัวเป็นเหล็กแข็งแรง";
                if (empty($autoUnit)) $autoUnit = 'ด้าม';
            } else {
                $specs = 'ความเข้มไส้ดินสอ 2B เหลาง่าย ไส้ไม่แตกหักง่าย เหมาะสำหรับทำข้อสอบและเขียนทั่วไป';
                if (empty($autoUnit)) $autoUnit = 'แท่ง';
            }
        } elseif (mb_strpos($n, 'ยางลบ') !== false) {
            $specs = 'ทำจากพลาสติก PVC คุณภาพดี ลบสะอาด ไม่ทำลายเนื้อกระดาษ ขยะยางลบเกาะตัวเป็นก้อน';
            if (empty($autoUnit)) $autoUnit = 'ก้อน';
        }
        // 5. คัตเตอร์
        elseif (mb_strpos($n, 'คัตเตอร์') !== false) {
            if (mb_strpos($n, 'ใบมีด') !== false) {
                $specs = "ขนาด " . ($sizeMm ?? '18 มม.') . " ทำจากเหล็กกล้าเกรดพรีเมียม ทำมุม 45 องศา คมทนทาน บรรจุ 10 ใบ/กล่อง";
                if (empty($autoUnit)) $autoUnit = 'กล่อง';
            } else {
                $specs = 'ขนาดใหญ่ ด้ามสแตนเลสหุ้มพลาสติกแข็ง มีระบบล็อกใบมีดอัตโนมัติ ใบมีดทำจากเหล็กกล้าคมทนทาน';
                if (empty($autoUnit)) $autoUnit = 'ด้าม';
            }
        }
        // 6. กระดาษ
        elseif (mb_strpos($n, 'กระดาษ') !== false) {
            $gsm = $sizeGsm ?? (mb_strpos($n, '80') !== false ? '80 แกรม' : '70 แกรม');
            if (mb_strpos($n, 'a4') !== false || mb_strpos($n, 'เอ4') !== false) {
                $specs = "ขนาด 210 x 297 มม. (A4) หนา {$gsm} บรรจุ 500 แผ่น/รีม เนื้อกระดาษขาวเรียบลื่น ถนอมสายตา พิมพ์ได้ 2 หน้า มาตรฐานทางราชการ";
                if (empty($autoUnit)) $autoUnit = 'รีม';
            } elseif (mb_strpos($n, 'โพสต์อิท') !== false || mb_strpos($n, 'โน้ต') !== false) {
                $specs = 'ขนาด 3 x 3 นิ้ว แถบกาวติดแน่น ลอกออกได้ไม่ทิ้งคราบกาว บรรจุ 100 แผ่น/เล่ม';
                if (empty($autoUnit)) $autoUnit = 'เล่ม';
            } else {
                $specs = "เนื้อกระดาษคุณภาพดี หนา {$gsm} เหมาะสำหรับงานพิมพ์เอกสารและใช้งานในสำนักงาน";
                if (empty($autoUnit)) $autoUnit = 'รีม';
            }
        }
        // 7. แฟ้มเอกสาร
        elseif (mb_strpos($n, 'แฟ้ม') !== false) {
            $sp = $sizeInch ?? '2 นิ้ว';
            if (mb_strpos($n, 'ห่วง') !== false || mb_strpos($n, 'สันหนา') !== false) {
                $specs = "ขนาด A4 สันกว้าง {$sp} ปกแข็งหุ้มตราช้าง/พีวีซี คลิปเหล็กแข็งแรง ล็อกแน่น สันแฟ้มมีป้ายชื่อ";
                if (empty($autoUnit)) $autoUnit = 'เล่ม';
            } else {
                $specs = "ขนาด A4 สันกว้าง {$sp} ปกแข็งอย่างดี ถนอมเอกสารได้ดีเยี่ยม";
                if (empty($autoUnit)) $autoUnit = 'เล่ม';
            }
        }
        // 8. เทปกาว / กาว
        elseif (mb_strpos($n, 'เทป') !== false) {
            $w = $sizeInch ?? '1 นิ้ว';
            $specs = "แกน 3 นิ้ว หน้ากว้าง {$w} ความยาว 36 หลา กาวอะคริลิกเหนียวแน่น ไม่เหลืองกรอบ";
            if (empty($autoUnit)) $autoUnit = 'ม้วน';
        }
        // 9. ซองเอกสาร
        elseif (mb_strpos($n, 'ซอง') !== false) {
            if (mb_strpos($n, 'น้ำตาล') !== false || mb_strpos($n, 'ขยายข้าง') !== false || mb_strpos($n, 'a4') !== false) {
                $specs = 'ขนาด 9 x 12 นิ้ว (A4) กระดาษคราฟท์สีน้ำตาลอย่างหนา 110 แกรม พิมพ์ตราครุฑทางการ แถบกาวติดแน่น';
                if (empty($autoUnit)) $autoUnit = 'ซอง';
            } else {
                $specs = 'ขนาด 4.5 x 7 นิ้ว กระดาษปอนด์ขาวคุณภาพดี พิมพ์ตราครุฑทางการ บรรจุ 50 ซอง/แพ็ค';
                if (empty($autoUnit)) $autoUnit = 'แพ็ค';
            }
        }
        // 10. ตรายาง / แท่นประทับ
        elseif (mb_strpos($n, 'ตรายาง') !== false || mb_strpos($n, 'ตราปั๊ม') !== false) {
            $specs = 'ทำจากยางพาราคุณภาพดี ตัวอักษรคมชัด ด้ามจับพลาสติกแข็งทนทาน ทนต่อแรงกดใช้งาน';
            if (empty($autoUnit)) $autoUnit = 'อัน';
        } elseif (mb_strpos($n, 'ประทับ') !== false || mb_strpos($n, 'ตลับชาด') !== false) {
            $specs = 'เบอร์ 2 (7 x 11 ซม.) ตลับโลหะแข็งแรง หมึกสีน้ำเงิน/แดง คมชัด แห้งเร็ว ไม่ซึมเลอะเทอะ';
            if (empty($autoUnit)) $autoUnit = 'ตลับ';
        }
        // 11. หมึกพิมพ์
        elseif (mb_strpos($n, 'หมึก') !== false || mb_strpos($n, 'toner') !== false) {
            $specs = 'ตลับผงหมึกพิมพ์เลเซอร์คุณภาพสูง ให้งานพิมพ์สีดำเข้ม คมชัด พิมพ์ได้ประมาณ 1,500-2,000 หน้า';
            if (empty($autoUnit)) $autoUnit = 'ตลับ';
        }
        // 12. ถ่านไฟฉาย
        elseif (mb_strpos($n, 'ถ่าน') !== false || mb_strpos($n, 'แบตเตอรี่') !== false) {
            $type = mb_strpos($n, 'aaa') !== false ? 'AAA' : (mb_strpos($n, 'aa') !== false ? 'AA' : 'AA/AAA');
            $specs = "ขนาด {$type} กำลังไฟ 1.5V อัลคาไลน์ ให้พลังงานยาวนาน ปราศจากสารปรอทและแคดเมียม บรรจุ 4 ก้อน/แพ็ค";
            if (empty($autoUnit)) $autoUnit = 'แพ็ค';
        }
        // 13. โต๊ะ / เก้าอี้ / ตู้ / ชั้น (ครุภัณฑ์ & พัสดุ)
        elseif (mb_strpos($n, 'เก้าอี้') !== false) {
            $specs = 'พนักพิงและเบาะนั่งบุฟองน้ำฉีดขึ้นรูป หุ้มผ้า/หนัง พีวีซี ขาเหล็กชุบโครเมี่ยมพร้อมล้อเลื่อน แข็งแรงทนทาน';
            if (empty($autoUnit)) $autoUnit = 'ตัว';
        } elseif (mb_strpos($n, 'โต๊ะ') !== false) {
            $specs = 'โครงสร้างเหล็ก/ไม้เนื้อแข็ง แข็งแรงทนทาน หน้าโต๊ะปิดผิวลามิเนตกันน้ำและรอยขีดข่วน มีลิ้นชักล็อกได้';
            if (empty($autoUnit)) $autoUnit = 'ตัว';
        } elseif (mb_strpos($n, 'ตู้') !== false) {
            $specs = 'โครงสร้างเหล็กพ่นสีกันสนิมอย่างดี มือจับแบบกดล็อก แข็งแรง ทนทาน ได้มาตรฐานงานพัสดุราชการ';
            if (empty($autoUnit)) $autoUnit = 'ตู้';
        } else {
            $specs = 'ขนาดมาตรฐาน ผลิตจากวัสดุคุณภาพดี ได้มาตรฐาน เหมาะสำหรับงานพัสดุสำนักงานและใช้งานทั่วไป';
        }

        return ['specs' => $specs, 'unit' => $autoUnit];
    }
}
