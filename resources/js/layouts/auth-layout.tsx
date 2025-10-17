import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';

export default function AuthLayout({ children, title, description, wide, ...props }: { children: React.ReactNode; title: string; description: string; wide?: boolean }) {
    return (
        <AuthLayoutTemplate title={title} description={description} wide={wide} {...props}>
            {children}
        </AuthLayoutTemplate>
    );
}
