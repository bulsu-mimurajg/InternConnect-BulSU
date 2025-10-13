import * as React from "react"
import { format } from "date-fns"
import { CalendarIcon, ClockIcon, XIcon } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { cn } from "@/lib/utils"

interface DateTimePickerProps {
  value?: Date | null
  onChange?: (date: Date | null) => void
  placeholder?: string
  disabled?: boolean
  className?: string
  error?: boolean
}

export function DateTimePicker({
  value,
  onChange,
  placeholder = "Select date and time",
  disabled = false,
  className,
  error = false,
}: DateTimePickerProps) {
  const [open, setOpen] = React.useState(false)
  const [tempDate, setTempDate] = React.useState<Date | undefined>(value || undefined)
  const [tempTime, setTempTime] = React.useState<string>("")

  // Update temp values when value prop changes
  React.useEffect(() => {
    if (value) {
      setTempDate(value)
      setTempTime(format(value, "HH:mm"))
    } else {
      setTempDate(undefined)
      setTempTime("")
    }
  }, [value])

  const handleDateSelect = (date: Date | undefined) => {
    setTempDate(date)
  }

  const handleTimeChange = (timeValue: string) => {
    setTempTime(timeValue)
  }

  const handleApply = () => {
    if (tempDate && tempTime) {
      const [hours, minutes] = tempTime.split(":").map(Number)
      const newDateTime = new Date(tempDate)
      newDateTime.setHours(hours || 0, minutes || 0, 0, 0)
      onChange?.(newDateTime)
    } else if (tempDate) {
      onChange?.(tempDate)
    }
    setOpen(false)
  }

  const handleCancel = () => {
    // Reset to original values
    if (value) {
      setTempDate(value)
      setTempTime(format(value, "HH:mm"))
    } else {
      setTempDate(undefined)
      setTempTime("")
    }
    setOpen(false)
  }

  const formatDisplayValue = () => {
    if (!value) return placeholder
    const dateStr = format(value, "MMM dd, yyyy")
    const timeStr = format(value, "HH:mm")
    return `${dateStr} at ${timeStr}`
  }

  return (
    <div className={cn("space-y-2", className)}>
      <Dialog open={open} onOpenChange={setOpen}>
        <DialogTrigger asChild>
          <Button
            variant="outline"
            className={cn(
              "w-full justify-start text-left font-normal h-10",
              !value && "text-muted-foreground",
              error && "border-destructive focus-visible:ring-destructive"
            )}
            disabled={disabled}
          >
            <CalendarIcon className="mr-2 h-4 w-4" />
            {formatDisplayValue()}
          </Button>
        </DialogTrigger>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <CalendarIcon className="h-5 w-5" />
              Select Date & Time
            </DialogTitle>
            <DialogDescription>
              Choose a date from the calendar and set the time for your deadline.
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-6">
            <div className="flex justify-center">
              <Calendar
                mode="single"
                selected={tempDate}
                onSelect={handleDateSelect}
                initialFocus
                className="[--cell-size:2.5rem]"
                classNames={{
                  day: "h-10 w-10 text-sm font-medium cursor-pointer hover:bg-accent hover:text-accent-foreground rounded-md",
                  day_selected: "bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground focus:bg-primary focus:text-primary-foreground",
                  day_today: "bg-accent text-accent-foreground font-bold",
                  day_outside: "text-muted-foreground opacity-50",
                  day_disabled: "text-muted-foreground opacity-50 cursor-not-allowed",
                  day_hidden: "invisible",
                  table: "w-full border-collapse",
                  week: "mt-1 flex w-full",
                  weekday: "text-muted-foreground flex-1 select-none rounded-md text-xs font-normal h-8 flex items-center justify-center",
                  month_caption: "flex h-10 w-full items-center justify-center px-2 text-sm font-semibold",
                  button_previous: "h-10 w-10 cursor-pointer",
                  button_next: "h-10 w-10 cursor-pointer",
                }}
              />
            </div>
            <div className="space-y-3">
              <Label htmlFor="time" className="text-sm font-medium">
                Time
              </Label>
              <div className="flex items-center justify-center space-x-2">
                <ClockIcon className="h-4 w-4 text-muted-foreground" />
                <Input
                  id="time"
                  type="time"
                  value={tempTime}
                  onChange={(e) => handleTimeChange(e.target.value)}
                  className="h-10 text-sm w-32 text-center"
                />
              </div>
            </div>
            <div className="flex justify-end space-x-3 pt-4 border-t">
              <Button
                variant="outline"
                onClick={handleCancel}
                className="h-9 px-4"
              >
                Cancel
              </Button>
              <Button
                onClick={handleApply}
                disabled={!tempDate}
                className="h-9 px-4"
              >
                Apply
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  )
}
