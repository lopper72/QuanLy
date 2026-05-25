export const categoryLabels = {
  gross_motor: 'Vận động thô',
  fine_motor: 'Vận động tinh',
  sensory: 'Giác quan',
  communication: 'Giao tiếp',
  cognitive: 'Nhận thức',
  social: 'Xã hội',
  self_care: 'Tự chăm sóc',
}

export const difficultyLabels = {
  easy: 'Dễ',
  medium: 'Trung bình',
  hard: 'Khó',
}

export const statusLabels = {
  pending: 'Chưa thực hiện',
  missed: 'Chưa hoàn thành',
  planned: 'Đã lên lịch',
  in_progress: 'Đang thực hiện',
  completed: 'Hoàn thành',
  skipped: 'Bỏ qua',
  not_completed: 'Chưa hoàn thành',
  need_help: 'Cần hỗ trợ',
}

export const childStatusLabels = {
  active: 'Đang can thiệp',
  paused: 'Tạm nghỉ',
  stopped: 'Dừng can thiệp',
  voided: 'Ngừng can thiệp',
}

export const itemStatusLabels = {
  pending: 'Chưa thực hiện',
  missed: 'Chưa hoàn thành',
  not_started: 'Chưa bắt đầu',
  completed: 'Hoàn thành',
  partially_completed: 'Hoàn thành một phần',
  skipped: 'Bỏ qua',
}

export const severityLabels = {
  low: 'Nhẹ',
  medium: 'Trung bình',
  high: 'Cao',
}

export const behaviorTypeLabels = {
  tantrum: 'Ăn vạ',
  avoidance: 'Né tránh',
  sensory_seeking: 'Tìm kiếm cảm giác',
  aggression: 'Hành vi gây hấn',
  self_stimulation: 'Tự kích thích',
  difficulty_transitioning: 'Khó chuyển hoạt động',
  transition_difficulty: 'Khó chuyển hoạt động',
  poor_sleep: 'Ngủ kém',
  picky_eating: 'Kén ăn',
  withdrawal: 'Thu mình',
  hyperactivity: 'Tăng động',
  noncompliance: 'Không tuân thủ',
  other: 'Khác',
}

export const diagnosisLevelLabels = {
  mild: 'Nhẹ',
  moderate: 'Trung bình',
  severe: 'Nặng',
  level_1: 'Mức 1',
  level_2: 'Mức 2',
  level_3: 'Mức 3',
}

export const weekdayLabels = {
  Mon: 'Thứ 2',
  Tue: 'Thứ 3',
  Wed: 'Thứ 4',
  Thu: 'Thứ 5',
  Fri: 'Thứ 6',
  Sat: 'Thứ 7',
  Sun: 'Chủ nhật',
  Monday: 'Thứ 2',
  Tuesday: 'Thứ 3',
  Wednesday: 'Thứ 4',
  Thursday: 'Thứ 5',
  Friday: 'Thứ 6',
  Saturday: 'Thứ 7',
  Sunday: 'Chủ nhật',
}

export const reportTypeLabels = {
  daily: 'Hằng ngày',
  weekly: 'Hằng tuần',
  monthly: 'Hằng tháng',
  custom: 'Tùy chỉnh',
  weekly_summary: 'Báo cáo tuần',
  monthly_summary: 'Báo cáo tháng',
  quarterly_summary: 'Báo cáo quý',
  progress_update: 'Cập nhật tiến bộ',
  behavior_overview: 'Tổng quan hành vi',
}

export const skillLabels = {
  gross_motor: 'Vận động thô',
  fine_motor: 'Vận động tinh',
  sensory: 'Giác quan',
  communication: 'Giao tiếp',
  cognitive: 'Nhận thức',
  social: 'Xã hội',
  self_care: 'Tự chăm sóc',
  receptive_language: 'Ngôn ngữ tiếp nhận',
  expressive_language: 'Ngôn ngữ biểu đạt',
  balance: 'Thăng bằng',
  problem_solving: 'Giải quyết vấn đề',
  self_regulation: 'Tự điều chỉnh',
  social_interaction: 'Tương tác xã hội',
  sensory_processing: 'Xử lý giác quan',
  attention: 'Chú ý',
  imitation: 'Bắt chước',
  play_skill: 'Kỹ năng chơi',
}

export const assessmentLevelLabels = {
  emerging: 'Đang hình thành',
  developing: 'Đang phát triển',
  proficient: 'Thành thạo',
  mastery: 'Làm chủ kỹ năng',
  achieved: 'Đã đạt được',
  regression: 'Thoái lui',
}

export const genderLabels = {
  male: 'Nam',
  female: 'Nữ',
  non_binary: 'Khác',
  Male: 'Nam',
  Female: 'Nữ',
  Other: 'Khác',
}

export const labelFor = (labels, value, fallback = 'Chưa xác định') => {
  if (value === null || value === undefined || value === '') {
    return fallback
  }

  return labels[value] || value
}


