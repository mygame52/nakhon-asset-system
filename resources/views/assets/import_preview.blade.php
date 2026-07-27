@extends('layouts.app')

@section('page_title', 'จัดการข้อมูลก่อนนำเข้า')
@section('page_description', 'กรุณาตรวจสอบ แก้ไข หรือลบข้อมูลที่ไม่ต้องการนำเข้าระบบ')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8" x-data="{ 
        rows: {{ json_encode($previewRows ?? []) }},
        removeRow(index) {
            this.rows.splice(index, 1);
        }
    }">
    <div class="p-6 border-b border-gray-100 bg-gray-50/30">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-edit text-orange-500 mr-2"></i> ตัวจัดการตารางข้อมูลขนาดย่อม</h2>
            <div class="text-sm text-gray-500 bg-white border border-gray-200 px-4 py-2 rounded-xl shadow-sm flex items-center gap-3">
                <i class="fas fa-file-excel text-emerald-600 text-lg"></i> 
                <div>พบข้อมูลทั้งหมด <span x-text="rows.length" class="font-bold text-emerald-600 text-base"></span> รายการ</div>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2"><i class="fas fa-info-circle mr-1"></i> แตะที่ข้อความในตารางเพื่อแก้ไข, กดวงแดงรูปถังขยะเพื่อลบแถวที่ไม่ต้องการออกจากรายการ</p>
    </div>

    <div class="overflow-x-auto min-h-[400px]">
        <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-bold">
                    <th class="p-4 w-28">วันที่</th>
                    <th class="p-4 px-6 w-32">รหัสฯ</th>
                    <th class="p-4 w-32">ประเภทครุภัณฑ์</th>
                    <th class="p-4 w-32">ยี่ห้อ</th>
                    <th class="p-4 w-40">รายการ (ชนิด/ขนาด)</th>
                    <th class="p-4 w-28 text-right">ราคา</th>
                    <th class="p-4 w-28">ได้มาโดย</th>
                    <th class="p-4 w-28">แผนก</th>
                    <th class="p-4 w-28">สถานที่</th>
                    <th class="p-4 w-24 text-center">สถานะ</th>
                    <th class="p-4 w-16 text-center">ลบ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100/80 text-sm">
                <!-- Fallback when exhausted -->
                <tr x-show="rows.length === 0" x-cloak>
                    <td colspan="11" class="p-12 text-center text-gray-400">
                        <i class="fas fa-folder-open text-4xl mb-3 opacity-20 block"></i>
                        ไม่มีข้อมูลเหลืออยู่ในรายการนำเข้า
                    </td>
                </tr>
                
                <!-- Reactive Rows Loop -->
                <template x-for="(row, index) in rows" :key="index">
                    <tr class="hover:bg-orange-50/30 transition-colors group">
                        <td class="p-4 relative">
                            <input type="text" x-model="row[0]" class="w-full text-xs bg-transparent border border-transparent focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded px-2 py-1 text-gray-600 transition-all" placeholder="วันที่">
                        </td>
                        <td class="p-4 px-6 relative">
                            <input type="text" x-model="row[1]" class="w-full text-sm bg-transparent border border-transparent focus:border-orange-300 focus:bg-white focus:ring-2 focus:orange-100 rounded px-2 py-1 text-purple-700 font-bold transition-all" placeholder="รหัสทะเบียน">
                        </td>
                        <td class="p-4 relative">
                            <input type="text" x-model="row[2]" class="w-full text-sm bg-transparent border border-transparent focus:border-blue-200 focus:bg-white focus:ring-2 focus:ring-blue-50 rounded px-2 py-1 text-gray-600 transition-all" placeholder="ประเภทครุภัณฑ์">
                        </td>
                        <td class="p-4 relative">
                            <input type="text" x-model="row[3]" class="w-full text-sm bg-transparent border border-transparent focus:border-blue-200 focus:bg-white focus:ring-2 focus:ring-blue-50 rounded px-2 py-1 text-gray-600 transition-all" placeholder="ยี่ห้อ">
                        </td>
                        <td class="p-4 relative">
                            <input type="text" x-model="row[4]" class="w-full text-sm bg-transparent border border-transparent focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded px-2 py-1 font-medium text-gray-800 transition-all" placeholder="ชื่อรายการ">
                        </td>
                        <td class="p-4 relative">
                            <div class="relative">
                                <input type="number" step="0.01" x-model="row[5]" class="w-full text-sm text-right bg-transparent border border-transparent focus:border-emerald-300 focus:bg-white focus:ring-2 focus:ring-emerald-100 rounded py-1 pl-2 pr-6 font-medium text-emerald-600 transition-all" placeholder="0.00">
                                <span class="absolute right-2 top-1.5 text-[10px] text-gray-400 pointer-events-none">฿</span>
                            </div>
                        </td>
                        <td class="p-4 relative">
                            <input type="text" x-model="row[6]" class="w-full text-sm bg-transparent border border-transparent focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded px-2 py-1 text-gray-600 transition-all" placeholder="วิธีการได้มา">
                        </td>
                        <td class="p-4 relative">
                            <input type="text" x-model="row[7]" class="w-full text-sm bg-transparent border border-transparent focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded px-2 py-1 text-gray-600 transition-all" placeholder="แผนก">
                        </td>
                        <td class="p-4 relative">
                            <input type="text" x-model="row[8]" class="w-full text-sm bg-transparent border border-transparent focus:border-blue-300 focus:bg-white focus:ring-2 focus:ring-blue-100 rounded px-2 py-1 text-gray-600 transition-all" placeholder="สถานที่">
                        </td>
                        <td class="p-4 relative">
                            <input type="text" x-model="row[9]" class="w-full text-xs text-center bg-blue-50/50 text-blue-700 px-2 py-1.5 rounded-full border border-blue-100 focus:bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 font-semibold transition-all" placeholder="ใช้งาน">
                        </td>
                        <td class="p-4 text-center">
                            <button type="button" @click="removeRow(index)" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-colors shadow-sm md:opacity-50 md:group-hover:opacity-100 focus:opacity-100 mx-auto">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Actions -->
    <div class="p-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 flex-wrap">
        <a href="{{ route('assets.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors cursor-pointer shadow-sm">
            <i class="fas fa-times mr-2"></i> ยกเลิกทิ้งไปทั้งหมด
        </a>
        
        <form action="{{ route('assets.import.process-json') }}" method="POST" class="m-0" x-show="rows.length > 0">
            @csrf
            <!-- Payload Transport -->
            <input type="hidden" name="payload" x-bind:value="JSON.stringify(rows)">
            <input type="hidden" name="path" value="{{ $path }}">
            
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-pink-500 text-white font-semibold shadow-md shadow-orange-500/30 hover:from-orange-600 hover:to-pink-600 transition-all active:scale-95 group">
                <i class="fas fa-cloud-upload-alt mr-2 group-hover:-translate-y-0.5 transition-transform"></i> นำเข้าข้อมูลทั้งหมด (<span x-text="rows.length"></span>)
            </button>
        </form>
    </div>
</div>
@endsection
