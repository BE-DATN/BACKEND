<?php

namespace App\Exports;

use DateTime;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminOrdersExport implements
     FromArray
    ,WithHeadings
    ,ShouldAutoSize
    ,WithStyles
    // WithMapping,
{
    protected $data = [];
    protected $titles = [];
    public function __construct($data, $title)
    {
        $this->data = array_map(function ($item) {
            unset($item['id']);
            $item['created_at'] = new DateTime(array_get($item, 'created_at'));
            $item['total_amount'] = number_format(array_get($item, 'total_amount'), 0, 0);
            $item['order_status'] = array_get($item, 'order_status') == 1 ? "Đã thanh toán" : "Chưa thanh toán";
            $item['voucher'] = array_get($item, 'voucher') == "null" ? "------" : array_get($item, 'voucher');
            $item['created_at'] = array_get($item, 'created_at')->format('Y-m-d / H:i:s');
            return $item;
        }, $data);

        $this->titles = $title;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'size' => '12',
                'italic' => false,
                'strikethrough' => false,
                'color' => [
                    'rgb' => 'ffffff'
                ]
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    // 'color' => ['rgb' => 'DBDBDB']
                    'color' => ['rgb' => 'ffffff']
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'ff7731',
                ],
            ],
            // 'alignment' => [
            //     'horizontal' => Alignment::HORIZONTAL_CENTER,
            //     'vertical' => Alignment::VERTICAL_CENTER,
            //     'wrapText' => true,
            // ],
        ]);
        $sheet->getStyle('A2:G' . (count($this->data) + 1))->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E1FFF0',
                ],
            ],
        ]);
        return [
        ];
    }
    // public function map($invoice): array
    // {
    //     return [];
    // }
    public function headings(): array
    {
        return [
            "Khách Hàng",
            "Mã Đơn Hàng",
            "Mã Giảm Giá",
            "Trạng Thái Đơn Hàng",
            "Tổng Tiền",
            "Phương thức thanh toán",
            "Ngày Tạo",
        ];
    }
    public function array(): array
    {
        return $this->data;
    }
}
