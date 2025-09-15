import { Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

// breadcrumbs is unused, so we'll remove it

interface Student {
    id: number | string;
    student_number: string;
    first_name: string;
    middle_name?: string;
    last_name: string;
    section_id: number;
    section: string;
    specialization?: string;
}

interface Section {
    section_id: number;
    section_name: string;
}

interface Props {
    student: Student;
    sections: Section[];
}

export default function EditStudent({ student, sections }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        student_number: student.student_number,
        first_name: student.first_name,
        middle_name: student.middle_name || '',
        last_name: student.last_name,
        section_id: student.section_id,
        specialization: student.specialization || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/student/${student.id}`);
    };

    return (
        <AdminLayout>
            <Head title="Edit Student" />

            <div className="max-w-2xl mx-auto">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Student Information</CardTitle>
                        <CardDescription>
                            Update the student's information below.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="student_number">Student Number</Label>
                                    <Input
                                        id="student_number"
                                        type="text"
                                        value={data.student_number}
                                        onChange={(e) => setData('student_number', e.target.value)}
                                        className={errors.student_number ? 'border-red-500' : ''}
                                    />
                                    {errors.student_number && (
                                        <p className="text-red-500 text-sm mt-1">{errors.student_number}</p>
                                    )}
                                </div>
                                <div>
                                    <Label htmlFor="section_id">Section</Label>
                                    <Select
                                        value={data.section_id.toString()}
                                        onValueChange={(value) => setData('section_id', parseInt(value))}
                                    >
                                        <SelectTrigger className={errors.section_id ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Select a section" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {sections.map((section) => (
                                                <SelectItem key={section.section_id} value={section.section_id.toString()}>
                                                    {section.section_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.section_id && (
                                        <p className="text-red-500 text-sm mt-1">{errors.section_id}</p>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <Label htmlFor="first_name">First Name</Label>
                                    <Input
                                        id="first_name"
                                        type="text"
                                        value={data.first_name}
                                        onChange={(e) => setData('first_name', e.target.value)}
                                        className={errors.first_name ? 'border-red-500' : ''}
                                    />
                                    {errors.first_name && (
                                        <p className="text-red-500 text-sm mt-1">{errors.first_name}</p>
                                    )}
                                </div>
                                <div>
                                    <Label htmlFor="middle_name">Middle Name</Label>
                                    <Input
                                        id="middle_name"
                                        type="text"
                                        value={data.middle_name}
                                        onChange={(e) => setData('middle_name', e.target.value)}
                                        className={errors.middle_name ? 'border-red-500' : ''}
                                    />
                                    {errors.middle_name && (
                                        <p className="text-red-500 text-sm mt-1">{errors.middle_name}</p>
                                    )}
                                </div>
                                <div>
                                    <Label htmlFor="last_name">Last Name</Label>
                                    <Input
                                        id="last_name"
                                        type="text"
                                        value={data.last_name}
                                        onChange={(e) => setData('last_name', e.target.value)}
                                        className={errors.last_name ? 'border-red-500' : ''}
                                    />
                                    {errors.last_name && (
                                        <p className="text-red-500 text-sm mt-1">{errors.last_name}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="specialization">Specialization</Label>
                                <Input
                                    id="specialization"
                                    type="text"
                                    value={data.specialization}
                                    onChange={(e) => setData('specialization', e.target.value)}
                                    className={errors.specialization ? 'border-red-500' : ''}
                                />
                                {errors.specialization && (
                                    <p className="text-red-500 text-sm mt-1">{errors.specialization}</p>
                                )}
                            </div>

                            <div className="flex justify-end space-x-3 pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => window.history.back()}
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-blue-500 hover:bg-blue-600"
                                >
                                    {processing ? 'Updating...' : 'Update Student'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
