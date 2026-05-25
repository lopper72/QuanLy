<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Báo cáo Tuần - {{ $child->first_name }} {{ $child->last_name }}</title>
    <style>
        body {
            font-family: Inter, "Be Vietnam Pro", "Noto Sans", "Segoe UI", Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #4f46e5;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .grid-item {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #6b7280;
            font-size: 12px;
            text-transform: none;
        }
        .info-value {
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stats-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .stat-item {
            display: inline-block;
            width: 24%;
            text-align: center;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
        }
        .stat-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            font-size: 12px;
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        td {
            padding: 8px;
            font-size: 13px;
            border-bottom: 1px solid #f3f4f6;
        }
        .badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-low { background-color: #d1fae5; color: #065f46; }
        .badge-medium { background-color: #fef3c7; color: #92400e; }
        .badge-high { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $statusLabels = [
            'planned' => 'Đã lên lịch',
            'in_progress' => 'Đang thực hiện',
            'completed' => 'Hoàn thành',
            'skipped' => 'Bỏ qua',
            'not_started' => 'Chưa bắt đầu',
            'partially_completed' => 'Hoàn thành một phần',
        ];
        $severityLabels = [
            'low' => 'Nhẹ',
            'medium' => 'Trung bình',
            'high' => 'Cao',
        ];
        $levelLabels = [
            'emerging' => 'Đang hình thành',
            'developing' => 'Đang phát triển',
            'proficient' => 'Thành thạo',
            'mastery' => 'Làm chủ kỹ năng',
            'achieved' => 'Đã đạt được',
            'regression' => 'Thoái lui',
        ];
    @endphp

    <div class="header">
        <h1>Báo cáo Can thiệp Tuần</h1>
        <p>{{ $date_range['start'] }} đến {{ $date_range['end'] }}</p>
    </div>

    <div class="section">
        <div class="grid">
            <div class="grid-item">
                <div class="info-label">Tên trẻ</div>
                <div class="info-value">{{ $child->first_name }} {{ $child->last_name }}</div>
                
                <div class="info-label">Ngày sinh</div>
                <div class="info-value">{{ $child->date_of_birth }}</div>
            </div>
            <div class="grid-item">
                <div class="info-label">Báo cáo được tạo</div>
                <div class="info-value">{{ $generated_at }}</div>
                
                <div class="info-label">Trạng thái</div>
                <div class="info-value">Tổng kết Tuần</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Tổng kết Tập luyện</div>
        <div class="stats-box">
            <div class="stat-item">
                <div class="stat-value">{{ $training['total_sessions'] }}</div>
                <div class="stat-label">Tổng số buổi</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $training['completed_sessions'] }}</div>
                <div class="stat-label">Đã hoàn thành</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $training['completion_rate'] }}%</div>
                <div class="stat-label">Tỷ lệ thành công</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $training['total_minutes'] }}</div>
                <div class="stat-label">Tổng số phút</div>
            </div>
        </div>
        
        @if(count($training['sessions']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Thời lượng</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @foreach($training['sessions'] as $session)
                <tr>
                    <td>{{ $session['date'] }}</td>
                    <td>{{ $session['duration'] }} phút</td>
                    <td>{{ $statusLabels[$session['status']] ?? $session['status'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="font-size: 13px; color: #666;">Không có buổi tập nào được ghi nhận trong giai đoạn này.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Theo dõi Hành vi</div>
        <div class="stats-box">
            <div class="stat-item" style="width: 32%;">
                <div class="stat-value">{{ $behavior['total_incidents'] }}</div>
                <div class="stat-label">Tổng số sự cố</div>
            </div>
            <div class="stat-item" style="width: 32%;">
                <div class="stat-value">{{ $behavior['severity_counts']['high'] }}</div>
                <div class="stat-label">Mức độ cao</div>
            </div>
            <div class="stat-item" style="width: 32%;">
                <div class="stat-value">{{ count($behavior['top_behaviors']) > 0 ? array_key_first($behavior['top_behaviors']) : 'Chưa có' }}</div>
                <div class="stat-label">Thường xuyên nhất</div>
            </div>
        </div>

        @if(count($behavior['logs']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Loại hành vi</th>
                    <th>Mức độ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($behavior['logs'] as $log)
                <tr>
                    <td>{{ $log['date'] }}</td>
                    <td>{{ $log['type'] }}</td>
                    <td>
                        <span class="badge badge-{{ $log['severity'] }}">
                            {{ $severityLabels[$log['severity']] ?? $log['severity'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="font-size: 13px; color: #666;">Không có sự cố hành vi nào được ghi nhận trong giai đoạn này.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Tiến độ Đánh giá Mới nhất</div>
        @if(count($assessment['skills']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Kỹ năng</th>
                    <th>Điểm hiện tại</th>
                    <th>Cấp độ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessment['skills'] as $skill)
                <tr>
                    <td>{{ $skill['name'] }}</td>
                    <td>{{ $skill['score'] }}/100</td>
                    <td>{{ $levelLabels[$skill['level']] ?? $skill['level'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="font-size: 13px; color: #666;">Không có dữ liệu đánh giá.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Ghi chú & Khuyến nghị</div>
        <div style="border: 1px solid #e5e7eb; padding: 15px; min-height: 100px; font-size: 13px; color: #4b5563;">
            <em>Không có ghi chú cụ thể nào được cung cấp cho giai đoạn này.</em>
        </div>
    </div>

    <div class="footer">
        Hệ thống Quản lý Can thiệp Trẻ em - Báo cáo Bảo mật
    </div>
</body>
</html>
