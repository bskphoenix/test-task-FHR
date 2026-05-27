#include <fcntl.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <sys/mman.h>
#include <sys/stat.h>
#include <unistd.h>

/** Один проход пузырька; возвращает новый upper_bound */
static int32_t bubble_pass(int32_t *data, int32_t upper_bound)
{
    int32_t last_swap = 0;

    for (int32_t i = 0; i < upper_bound; i++) {
        if (data[i] > data[i + 1]) {
            int32_t tmp = data[i];
            data[i] = data[i + 1];
            data[i + 1] = tmp;
            last_swap = i + 1;
        }
    }

    return last_swap > 0 ? last_swap - 1 : 0;
}

/** Пакет проходов по файлу int32 LE; -1 — ошибка, 1 — готово, 0 — продолжить */
static int32_t bubble_sort_file_batch(const char *path, int32_t *pass, int32_t *upper_bound, int32_t max_passes)
{
    int fd = open(path, O_RDWR);

    if (fd < 0) {
        return -1;
    }

    struct stat st;

    if (fstat(fd, &st) != 0 || st.st_size < 8 || st.st_size % 4 != 0) {
        close(fd);

        return -1;
    }

    size_t size = (size_t) st.st_size;
    int32_t *data = mmap(NULL, size, PROT_READ | PROT_WRITE, MAP_SHARED, fd, 0);

    close(fd);

    if (data == MAP_FAILED) {
        return -1;
    }

    int32_t count = (int32_t) (size / 4);

    if (*upper_bound <= 0 || *upper_bound >= count) {
        *upper_bound = count - 1;
    }

    if (count < 2 || *upper_bound <= 0 || max_passes <= 0) {
        munmap(data, size);

        return 1;
    }

    for (int32_t done = 0; *upper_bound > 0 && done < max_passes; done++) {
        (*pass)++;
        *upper_bound = bubble_pass(data, *upper_bound);
    }

    int32_t completed = *upper_bound <= 0 ? 1 : 0;

    munmap(data, size);

    return completed;
}

/** CLI: bubble_sort <file> <max_passes> <pass> <upper_bound> */
int main(int argc, char **argv)
{
    if (argc < 5) {
        fprintf(stderr, "usage: %s <file> <max_passes> <pass> <upper_bound>\n", argv[0]);

        return 2;
    }

    int32_t pass = (int32_t) atoi(argv[3]);
    int32_t upper_bound = (int32_t) atoi(argv[4]);
    int32_t completed = bubble_sort_file_batch(argv[1], &pass, &upper_bound, (int32_t) atoi(argv[2]));

    if (completed < 0) {
        return 1;
    }

    printf("%d %d %d\n", completed, pass, upper_bound);

    return 0;
}
