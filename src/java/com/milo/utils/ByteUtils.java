package com.milo.utils;

import java.io.*;

public class ByteUtils {
    private static byte[] i32out = new byte[4];
    private static byte[] i64out = new byte[8];

    public static int byteArrayToInt(byte[] bytes) {
        return byteArrayToInt(bytes, 0);
    }

    public static int byteArrayToInt(byte[] bytes, int offset) {
        if (bytes.length - offset < 4) {
            throw new IllegalArgumentException("An integer must be 4 bytes in size.");
        }
        int n = 0;
        for (int i = 0; i < 4; ++i) {
            n <<= 8;
            n |= bytes[offset + i] & 0xFF;
        }
        return n;
    }

    public static String toString(ByteArrayInputStream bais) throws IOException {
        int length = bais.available();
        byte[] buff = new byte[length];
        bais.read(buff);

        return new String(buff);
    }

    public static byte[] getBytesFromFile(File file) throws IOException {
        InputStream is = new FileInputStream(file);

        long length = file.length();

        if (length > Integer.MAX_VALUE) {
            throw new IOException("File is way too big");
        }

        byte[] bytes = new byte[(int) length];

        int offset = 0;
        int numRead = 0;
        while (offset < bytes.length
                && (numRead = is.read(bytes, offset, bytes.length - offset)) >= 0) {
            offset += numRead;
        }

        if (offset < bytes.length) {
            throw new IOException("Could not completely read file " + file.getName());
        }

        is.close();
        return bytes;
    }
    public static byte[] intToByteArray(int i32) {
        i32out[0] = (byte)(0xff & (i32 >> 24));
        i32out[1] = (byte)(0xff & (i32 >> 16));
        i32out[2] = (byte)(0xff & (i32 >> 8));
        i32out[3] = (byte)(0xff & (i32));
        return i32out;
    }
    public static byte[] longToByteArray(long i64)  {
      i64out[0] = (byte)(0xff & (i64 >> 56));
      i64out[1] = (byte)(0xff & (i64 >> 48));
      i64out[2] = (byte)(0xff & (i64 >> 40));
      i64out[3] = (byte)(0xff & (i64 >> 32));
      i64out[4] = (byte)(0xff & (i64 >> 24));
      i64out[5] = (byte)(0xff & (i64 >> 16));
      i64out[6] = (byte)(0xff & (i64 >> 8));
      i64out[7] = (byte)(0xff & (i64));
      return i64out;
    }

}
